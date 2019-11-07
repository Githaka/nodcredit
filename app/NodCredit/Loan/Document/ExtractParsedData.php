<?php
namespace App\NodCredit\Loan\Document;

class ExtractParsedData
{

    /**
     * @param array $data
     * @return array|null
     */
    public static function extract(array $data)
    {

        if ($layoutModel = (int) array_get($data, 'layout_model', 0)) {
            $extractedData = static::extractUsingLayoutModel($layoutModel, $data);
        }
        else {
            $extractedData = static::extractUsingLoop($data);
        }

        // Customer name and account number are required
        if (! array_get($extractedData, 'customer_name') OR ! array_get($extractedData, 'account_number')) {
            return null;
        }

        return $extractedData;
    }

    /**
     * @param int $number
     * @param array $data
     * @return array|null
     */
    public static function extractUsingLayoutModel(int $number, array $data)
    {
        return [
            'customer_name' => array_get($data, "{$number}_customer_name"),
            'account_number' => static::getMatchValue(array_get($data, "{$number}_account_number", [])),
            'statement_period_start' => static::getFormattedValue(array_get($data, "{$number}_statement_period_start", [])),
            'statement_period_end' => static::getFormattedValue(array_get($data, "{$number}_statement_period_end", [])),
            'transactions' => array_get($data, 'transactions', []),
        ];
    }

    public static function extractUsingLoop(array $data)
    {

        $fields = [
            'customer_name',
            'account_number',
            'statement_period_start',
            'statement_period_end',
        ];

        for ($i = 1; $i < 6; $i++) {

            $group = [];

            foreach ($fields as $field) {

                // No value - skip
                if (! $fieldValue = array_get($data, $i . '_' . $field)) {
                    continue;
                }

                // New format
                if (is_array($fieldValue)) {

                    // Account number has "match" element
                    if ($field === 'account_number') {
                        $value = static::getMatchValue($fieldValue);
                    }
                    // Statement period date has "formatted" element
                    else if (in_array($field, ['statement_period_start', 'statement_period_end'])) {
                        $value = static::getFormattedValue($fieldValue);
                    }
                    else {
                        continue;
                    }

                }
                else {
                    $value = $fieldValue;
                }

                $group[$field] = $value;
            }

            if (array_get($group, 'customer_name') AND array_get($group, 'account_number')) {
                $group['transactions'] = array_get($data, 'transactions', []);

                return $group;
            }
        }

        return null;
    }

    public static function getFormattedValue($array)
    {
        return array_get($array, 'formatted');
    }

    public static function getMatchValue($array)
    {
        $value = '';

        if (count($array) > 1) {
            foreach ($array as $row) {
                $value .= array_get($row, 'match');
            }
        }
        else {
            $value = array_get($array, 'match');
        }

        return $value;
    }
}