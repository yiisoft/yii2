<?php


namespace yii\validators;


class FilterVarEmailValidator extends EmailValidator
{
    public $allowUnicode = true;

    private function checkDns(string $domain): bool
    {
        return checkdnsrr($domain . '.', 'MX') || checkdnsrr($domain . '.', 'A');
    }

    protected function validateValue($value)
    {
        if (!is_string($value)) {
            return [$this->message, []];
        }

        // Split name part
        if ($this->allowName && preg_match('/^(.*)\<(.+)\>$/s', $value, $matches)) {
            $value = $matches[2];
        }

        // Split host and local part.
        $parts = explode('@', $value);

        $host = array_pop($parts);
        $local = implode('@', $parts);

        // For international domain names we must use idn_to_ascii.
        if ($this->enableIDN) {
            $idn = idn_to_ascii($host, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46);
            if ($idn === false) {
                return [$this->message, []];
            } else {
                $host = $idn;
            }
        }

        if ($this->checkDNS && !$this->checkDns($host)) {
            return [$this->message, []];
        }

        $valid = filter_var("$local@$host", FILTER_VALIDATE_EMAIL, $this->allowUnicode ? FILTER_FLAG_EMAIL_UNICODE : 0);
        return $valid ? null : [$this->message, []];
    }


}