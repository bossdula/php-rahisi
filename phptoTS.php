<?php

namespace Core;

class DataView
{
    public function __construct()
    {
    }

    public static function displayTHead($headers, $hidden, $actions = false): array
    {
        $eliminate = ['id', 'row_id', 'txt_row_value'];

        $header_hidden_diff = array_diff($headers, $hidden);
        $header_hidden_diff = array_diff($header_hidden_diff, $eliminate);

        $trimmed_headers = [];

        foreach ($header_hidden_diff as $header) {
            $trimmed_headers[] = self::trimColumnNames($header);
        }

        if ($actions) {
            $trimmed_headers[] = 'Actions';
        }

        return $trimmed_headers;
    }

    // return the value of each property from given object to display
    public static function trimColumnNames($th, $body = false): string
    {
        $prefixes = ['txt', 'int', 'dbl', 'tar', 'dat', 'tim',];
        $suffixes = ['id'];

        $remove = ['txt_', 'int_', 'dbl_', 'tar_', 'dat_', 'tim_',];
        $capitalize = ['otp', 'id',];

        $trimmed = str_replace($remove, '', $th);

        if (!$body) {
            $trimmed = str_replace(['_id', 'mx_', 'opt_'], '', $trimmed);
            $trimmed = str_replace(['_', '-'], ' ', $trimmed);
            return in_array($trimmed, $capitalize) ? strtoupper($trimmed) : ucwords($trimmed);
        } else {
            if (str_contains($trimmed, 'opt_mx_')) {
                return strtolower($trimmed);
            } else {
                $trimmed = str_replace(['_', '-'], ' ', $trimmed);
                return in_array($trimmed, $capitalize) ? strtoupper($trimmed) : ucwords($trimmed);
            }
        }
    }

//    public static function displayTBody($object, $class, $cls_table, $hidden, $actions = [], $label_size = 'getSmallLabel', $labels = [], $is_pending = false, $formatters = [])
    public static function displayTBody($object, $headers, $hidden, $actions = [], $labels = [], $formatters = []): array
    {
        $cleaned_up_titles = self::trimHiddenData($headers, $hidden);
        $tmp_data = [];

//        print_r($object);
//        print_r($cleaned_up_titles);
//        exit;

        // Iterate over the $object array
        for ($i = 0; $i < count($object); $i++) {
            // Iterate over each key-value pair in the current row
            foreach ($object[$i] as $field => $value) {
//                print_r($field . ': ');
                $fk = [];
                $key = self::trimColumnNames($field, true);

//                print_r($key . '...');
                if (str_contains($field, 'opt_mx_')) {
                    if (array_key_exists($value, $labels[$key])) {
                        $fk = self::getFKValues($key, $value, $labels[$key]);
                    }
                }

//                print_r($field . '...');

                // Check if the key is in the $cleaned_up_titles array
                if (in_array($key, array_values($cleaned_up_titles))) {
                    if (empty($fk)) {
                        $tmp_data[$i][$key] = $value;
                    } else {
                        $tmp_data[$i][$key] = $fk;
                    }
                } /*else {
                    echo $key . ' not in cleaned up titles...';
                }*/

            }

//            if (count($actions) > 0) {
//                $row = $object[$i];
//                $row_id = $object[$i]['row_id'];
////                self::generateActionButtons($actions, $object_id, $class, $cls_table, $rows, $row_id);
//                $tmp_data[$i]['actions'] = self::generateActionButtons($actions, $row, $row_id);
//            }

        }
//        print_r($tmp_data);
//exit;
        return $tmp_data;
    }

    private static function trimHiddenData($header, $hidden): array
    {
        $eliminate = ['id', 'row_id', 'txt_row_value', 'txt_added_by'];

        $header_hidden_diff = array_diff($header, $hidden);
        return array_diff($header_hidden_diff, $eliminate);
    }

    private static function getFKValues($field, $value, $label): array
    {
        $trimmed_th = str_replace(['_id', 'mx_', 'opt_'], '', $field);
        $fk_values['type'] = 'fk';
        $fk_values['og_name'] = $field;
        $fk_values['display_name'] = $trimmed_th;
        $fk_values['og_value'] = $value;
        $fk_values['display_value'] = $label[$value]['value'];
        $fk_values['color'] = $label[$value]['color'] ?? '';
        return $fk_values;
    }

    private static function generateActionButtons($actions, $row, $row_id): array
    {
        $tmp_actions = [];

        for ($i = 0; $i < count($actions); $i++) {
            $tmp_actions[$i]['action'] = strtolower($actions[$i]['action']);
            $tmp_actions[$i]['name'] = $actions[$i]['name'];
            $tmp_actions[$i]['icon'] = $actions[$i]['icon'];
            $tmp_actions[$i]['color'] = $actions[$i]['color'];
            $tmp_actions[$i]['url'] = $actions[$i]['url']/* . DS . strtolower($actions[$i]['action']) . DS . $row_id*/
            ;

            if (isset($actions[$i]['disabled'])) {
                $check_disable = self::evaluateDisabledActions($row, $actions[$i]['disabled']);
                $condition = $check_disable['key'];
                $disable = $check_disable['value'];
            } else {
                $condition = 'none';
                $disable = 0;
            }

            $tmp_actions[$i]['parameter'] = $condition;
            $tmp_actions[$i]['disabled'] = $disable;
        }
        return $tmp_actions;
    }

//    private static function generateActionButtons($actions, $object_id, $class, $cls_table, $row, $row_id)
    private static function evaluateDisabledActions($row, $disabled_values): array
    {
        $evaluation = null;
        $fk = null;

        if (isset($disabled_values['OR'])) {
            $evaluation = 0;
            foreach ($disabled_values['OR'] as $key => $value) {
                $fk = $key;
                $field_value = $row[$key] ?? '';
                $values_is_array = is_array($value);
                if ($values_is_array) {
                    foreach ($value as $item) {

                        $evaluated = $field_value == $item;
                        $evaluation = $evaluated || $evaluation;
                    }
                } else {
                    $evaluated = $field_value == $value;
                    $evaluation = $evaluated || $evaluation;
                }
            }
            unset($disabled_values['OR']);
        }

        if (isset($disabled_values['AND'])) {
            if ($evaluation == null) $evaluation = 1;
            foreach ($disabled_values['AND'] as $key => $value) {
                $fk = $key;
                $field_value = $row[$key];
                $values_is_array = is_array($value);
                if ($values_is_array) {
                    foreach ($value as $item) {
                        $evaluated = $field_value == $item;
                        $evaluation = $evaluated && $evaluation;
                    }
                } else {
                    $evaluated = $field_value == $value;
                    $evaluation = $evaluated && $evaluation;
                }
            }
            unset($disabled_values['AND']);
        }

        if (count($disabled_values) > 0) {
            if ($evaluation == null) $evaluation = 1;
            foreach ($disabled_values as $key => $value) {
                $fk = $key;
                $field_value = $row[$key] ?? '';
                $values_is_array = is_array($value);
                if ($values_is_array) {
                    foreach ($value as $item) {
                        $evaluated = $field_value == $item;
                        $evaluation = $evaluated && $evaluation;
                    }
                } else {
                    $evaluated = $field_value == $value;
                    $evaluation = $evaluated && $evaluation;
                }
            }
        }

//        return $evaluation;
        return ['key' => $fk, 'value' => $evaluation];
    }

    private static function getActionName($action): string
    {
        print_r('Action: ' . $action);
        $action_name = $action;
//        if ($action == "post_edit") {
//            $action_name = "update";
//        } elseif ($action == "post_register_account") {
//            $action_name = "Add Account";
//        } elseif ($action == "post_add_float") {
//            $action_name = "Add Float";
//        } elseif ($action == "post_reset_pin") {
//            $action_name = "Reset Pin";
//        }
        if (substr($action_name, 0, 5) == "post_") {
            return ucwords(str_replace("_", " ", substr($action_name, 5)));
        } else {
            return ucwords(str_replace("_", " ", $action_name));
        }
    }

}
