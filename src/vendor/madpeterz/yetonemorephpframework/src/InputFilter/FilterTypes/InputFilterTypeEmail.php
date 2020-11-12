<?php

namespace YAPF\InputFilter\FilterTypes;

abstract class InputFilterTypeEmail extends InputFilterTypeHttp
{
    /**
     * filterEmail
     * Checks to see if a given string appears to be a vaild email
     * args: no_mailboxs
     * much faster but does not support gmail style + boxs.
     */
    protected function filterEmail(string $value, array $args = []): ?string
    {
        $this->failure = false;
        $this->testOK = true;
        if (in_array("no_mailboxs", $args) == true) {
        // fails on ALOT of vaild email addresses. but much faster
            if (strpos($value, "+") !== false) {
                $this->whyfailed = "no_mailboxs";
                return null;
            }
            return $this->filterEmail($value);
        } else {
            $allowed = true;
            $local_value = "";
            $mailbox_value = "";
            $domain_value = "";
            $bits = explode("@", $value);
            if (count($bits) == 2) {
                $domain_value = $bits[1];
                $mailbox = explode("+", $bits[0]);
                $local_value = $mailbox[0];
                if (count($mailbox) == 2) {
                    $mailbox_value = $mailbox[1];
                }
                $filter_testvalue = "" . $local_value . "@" . $domain_value . "";
                if (filter_var($filter_testvalue, FILTER_VALIDATE_EMAIL) !== false) {
                    if ($mailbox_value != "") {
                        $value = "" . $local_value . "+" . $mailbox_value . "@" . $domain_value . "";
                    }
                } else {
                    $this->whyfailed = "Failed vaildation after removing mailbox";
                    $allowed = false;
                }
            } else {
                $this->whyfailed = "Required @ missing";
                $allowed = false;
            }
            if ($allowed == true) {
                return $value;
            }
            return null;
        }
    }
}
