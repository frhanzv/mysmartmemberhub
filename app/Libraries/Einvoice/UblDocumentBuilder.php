<?php

namespace App\Libraries\Einvoice;

use App\Libraries\SettingsService;

/**
 * Builds a MyInvois-compatible UBL 2.1 JSON document (v1.0) for Invoice / Credit Note /
 * Debit Note / Refund Note. v1.0 is used because it skips digital-signature validation,
 * which is appropriate for ERP-side submissions in the current sandbox.
 *
 * Reference:
 *  - https://sdk.myinvois.hasil.gov.my/documents/invoice-v1-0/
 *  - https://sdk.myinvois.hasil.gov.my/types/
 *  - https://sdk.myinvois.hasil.gov.my/codes/
 */
class UblDocumentBuilder
{
    /** Map our internal document_type to UBL InvoiceTypeCode. */
    public const TYPE_CODES = [
        'invoice'          => '01',
        'credit_note'      => '02',
        'debit_note'       => '03',
        'refund_note'      => '04',
        'self_billed'      => '11',
        'self_credit_note' => '12',
        'self_debit_note'  => '13',
        'self_refund_note' => '14',
    ];

    /**
     * @param string     $documentType   one of self::TYPE_CODES keys
     * @param array      $invoice        invoices row (with member loaded under ['member'])
     * @param array      $member         members row
     * @param array      $plan           membership_plans row
     * @param float      $amount         positive total amount in MYR (the document level amount)
     * @param string|null $originalUuid  IRBM UUID of original invoice — required for credit/debit/refund notes
     * @param string|null $originalNo    e-Invoice code/number of original invoice — required for credit/debit/refund notes
     */
    public static function build(
        string $documentType,
        array $invoice,
        array $member,
        array $plan,
        float $amount,
        ?string $originalUuid = null,
        ?string $originalNo   = null
    ): array {
        if (! isset(self::TYPE_CODES[$documentType])) {
            throw new \InvalidArgumentException("Unknown document type: $documentType");
        }
        $code   = self::TYPE_CODES[$documentType];
        $issued = $invoice['issued_at'] ?? date('Y-m-d H:i:s');
        $ts     = strtotime($issued);
        $date   = gmdate('Y-m-d', $ts);
        $time   = gmdate('H:i:s\Z', $ts);

        $supplier = self::supplierParty();
        $buyer    = self::buyerParty($member);
        $line     = self::buildLine($plan, $amount);
        $totals   = self::buildTotals($amount, (float) $plan['tax_rate'], $plan['tax_type'] ?? '06');

        $invoiceObj = [
            'ID'                       => [['_' => $invoice['invoice_no']]],
            'IssueDate'                => [['_' => $date]],
            'IssueTime'                => [['_' => $time]],
            'InvoiceTypeCode'          => [['_' => $code, 'listVersionID' => '1.0']],
            'DocumentCurrencyCode'     => [['_' => 'MYR']],
            'TaxCurrencyCode'          => [['_' => 'MYR']],
            'AccountingSupplierParty'  => [['Party' => [$supplier]]],
            'AccountingCustomerParty'  => [['Party' => [$buyer]]],
            'InvoiceLine'              => [$line],
            'TaxTotal'                 => [$totals['taxTotal']],
            'LegalMonetaryTotal'       => [$totals['monetaryTotal']],
        ];

        // Reference original document for credit/debit/refund notes.
        if (in_array($documentType, ['credit_note','debit_note','refund_note','self_credit_note','self_debit_note','self_refund_note'], true)
            && $originalUuid && $originalNo
        ) {
            $invoiceObj['BillingReference'] = [[
                'InvoiceDocumentReference' => [[
                    'ID'  => [['_' => $originalNo]],
                    'UUID'=> [['_' => $originalUuid]],
                ]],
            ]];
        }

        return [
            '_D'      => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
            '_A'      => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
            '_B'      => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
            'Invoice' => [$invoiceObj],
        ];
    }

    /** Supplier party from settings. */
    private static function supplierParty(): array
    {
        $tin     = (string) SettingsService::get('einvoice.supplier.tin', '');
        $brn     = (string) SettingsService::get('einvoice.supplier.brn', '');
        $brnSch  = (string) SettingsService::get('einvoice.supplier.brn_scheme', 'BRN');
        $sst     = (string) SettingsService::get('einvoice.supplier.sst_no', '');
        $msic    = (string) SettingsService::get('einvoice.supplier.msic', '00000');
        $name    = (string) SettingsService::get('company.name', '');
        $email   = (string) SettingsService::get('einvoice.supplier.email', SettingsService::get('company.email', ''));
        $phone   = (string) SettingsService::get('einvoice.supplier.phone', SettingsService::get('company.phone', ''));

        $ids = [];
        if ($tin !== '') { $ids[] = ['ID' => [['_' => $tin, 'schemeID' => 'TIN']]]; }
        if ($brn !== '') { $ids[] = ['ID' => [['_' => $brn, 'schemeID' => $brnSch]]]; }
        if ($sst !== '') { $ids[] = ['ID' => [['_' => $sst, 'schemeID' => 'SST']]]; }

        $address = self::address(
            (string) SettingsService::get('einvoice.supplier.address1', ''),
            (string) SettingsService::get('einvoice.supplier.address2', ''),
            (string) SettingsService::get('einvoice.supplier.city', ''),
            (string) SettingsService::get('einvoice.supplier.postcode', ''),
            (string) SettingsService::get('einvoice.supplier.state', ''),
            (string) SettingsService::get('einvoice.supplier.country', 'MYS'),
        );

        return [
            'PartyIdentification'        => $ids,
            'PartyLegalEntity'           => [['RegistrationName' => [['_' => $name]]]],
            'PostalAddress'              => [$address],
            'Contact'                    => [[
                'Telephone'      => [['_' => self::normalizePhone($phone)]],
                'ElectronicMail' => [['_' => $email]],
            ]],
            'IndustryClassificationCode' => [[
                '_'    => $msic,
                'name' => (string) SettingsService::get('einvoice.supplier.activity', ''),
            ]],
        ];
    }

    /** Buyer party from member row. Falls back to "general public" for B2C members
     *  with no TIN. LHDN allows EI00000000010 as a generic individual TIN. */
    private static function buyerParty(array $member): array
    {
        $tin     = trim((string) ($member['tin'] ?? ''));
        $idNo    = trim((string) ($member['brn_or_nric'] ?? ($member['ic_no'] ?? '')));
        $regType = $member['registration_type'] ?? 'Individual';

        $idScheme = match ($regType) {
            'Company'    => 'BRN',
            'Individual' => 'NRIC',
            'Government' => 'BRN',
            default      => 'BRN',
        };

        if ($tin === '') {
            // Generic buyer TIN per LHDN docs for unknown individual buyers
            $tin = $regType === 'Company' ? 'EI00000000020' : 'EI00000000010';
        }
        if ($idNo === '') {
            $idNo = 'NA';
        }

        $ids = [
            ['ID' => [['_' => $tin,  'schemeID' => 'TIN']]],
            ['ID' => [['_' => $idNo, 'schemeID' => $idScheme]]],
        ];

        $address = self::address(
            (string) ($member['address_line1'] ?? ($member['address'] ?? 'NA')),
            (string) ($member['address_line2'] ?? ''),
            (string) ($member['city']           ?? 'Kuala Lumpur'),
            (string) ($member['postcode']       ?? '50000'),
            (string) ($member['state_code']     ?? '14'),
            (string) ($member['country_code']   ?? 'MYS'),
        );

        return [
            'PartyIdentification' => $ids,
            'PartyLegalEntity'    => [['RegistrationName' => [['_' => $member['name'] ?? 'NA']]]],
            'PostalAddress'       => [$address],
            'Contact'             => [[
                'Telephone'      => [['_' => self::normalizePhone((string) ($member['phone'] ?? '+60000000000'))]],
                'ElectronicMail' => [['_' => (string) ($member['email'] ?? 'na@example.com')]],
            ]],
        ];
    }

    private static function buildLine(array $plan, float $amount): array
    {
        $taxRate = (float) ($plan['tax_rate'] ?? 0);
        $taxAmt  = round($amount * $taxRate / 100, 2);
        $net     = round($amount - $taxAmt, 2);
        $taxType = (string) ($plan['tax_type'] ?? '06');

        return [
            'ID'                  => [['_' => '1']],
            'InvoicedQuantity'    => [['_' => 1, 'unitCode' => (string) ($plan['unit_code'] ?? 'MON')]],
            'LineExtensionAmount' => [['_' => $net, 'currencyID' => 'MYR']],
            'TaxTotal'            => [[
                'TaxAmount'        => [['_' => $taxAmt, 'currencyID' => 'MYR']],
                'TaxSubtotal'      => [[
                    'TaxableAmount' => [['_' => $net, 'currencyID' => 'MYR']],
                    'TaxAmount'     => [['_' => $taxAmt, 'currencyID' => 'MYR']],
                    'Percent'       => [['_' => $taxRate]],
                    'TaxCategory'   => [[
                        'ID'        => [['_' => $taxType]],
                        'TaxScheme' => [[
                            'ID' => [['_' => 'OTH', 'schemeID' => 'UN/ECE 5153', 'schemeAgencyID' => '6']],
                        ]],
                    ]],
                ]],
            ]],
            'Item' => [[
                'CommodityClassification' => [
                    ['ItemClassificationCode' => [['_' => (string) ($plan['classification_code'] ?? '022'), 'listID' => 'CLASS']]],
                ],
                'Description' => [['_' => (string) ($plan['name'] ?? 'Membership')]],
                'OriginCountry' => [['IdentificationCode' => [['_' => 'MYS']]]],
            ]],
            'Price'             => [[ 'PriceAmount' => [['_' => $amount, 'currencyID' => 'MYR']] ]],
            'ItemPriceExtension'=> [[ 'Amount'      => [['_' => $amount, 'currencyID' => 'MYR']] ]],
        ];
    }

    private static function buildTotals(float $amount, float $taxRate, string $taxType): array
    {
        $taxAmt = round($amount * $taxRate / 100, 2);
        $net    = round($amount - $taxAmt, 2);

        return [
            'taxTotal' => [
                'TaxAmount'   => [['_' => $taxAmt, 'currencyID' => 'MYR']],
                'TaxSubtotal' => [[
                    'TaxableAmount' => [['_' => $net,    'currencyID' => 'MYR']],
                    'TaxAmount'     => [['_' => $taxAmt, 'currencyID' => 'MYR']],
                    'Percent'       => [['_' => $taxRate]],
                    'TaxCategory'   => [[
                        'ID'        => [['_' => $taxType]],
                        'TaxScheme' => [[
                            'ID' => [['_' => 'OTH', 'schemeID' => 'UN/ECE 5153', 'schemeAgencyID' => '6']],
                        ]],
                    ]],
                ]],
            ],
            'monetaryTotal' => [
                'LineExtensionAmount' => [['_' => $net,    'currencyID' => 'MYR']],
                'TaxExclusiveAmount'  => [['_' => $net,    'currencyID' => 'MYR']],
                'TaxInclusiveAmount'  => [['_' => $amount, 'currencyID' => 'MYR']],
                'PayableAmount'       => [['_' => $amount, 'currencyID' => 'MYR']],
            ],
        ];
    }

    private static function address(string $l1, string $l2, string $city, string $post, string $state, string $country): array
    {
        $lines = [];
        if ($l1 !== '') { $lines[] = ['Line' => [['_' => $l1]]]; }
        if ($l2 !== '') { $lines[] = ['Line' => [['_' => $l2]]]; }
        if (empty($lines)) { $lines[] = ['Line' => [['_' => 'NA']]]; }

        return [
            'AddressLine'          => $lines,
            'CityName'             => [['_' => $city !== '' ? $city : 'Kuala Lumpur']],
            'PostalZone'           => [['_' => $post !== '' ? $post : '50000']],
            'CountrySubentityCode' => [['_' => $state !== '' ? $state : '14']],
            'Country'              => [[
                'IdentificationCode' => [['_' => $country !== '' ? $country : 'MYS', 'listID' => 'ISO3166-1', 'listAgencyID' => '6']],
            ]],
        ];
    }

    /** LHDN expects `+` followed by digits. Strip everything else. */
    private static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/[^\d]/', '', $phone) ?? '';
        if ($digits === '') { return '+60000000000'; }
        if (str_starts_with($digits, '0')) { $digits = '6' . $digits; } // local 0XX -> 60XX
        return '+' . $digits;
    }
}
