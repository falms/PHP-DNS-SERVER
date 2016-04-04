<?php

namespace yswery\DNS;

use \Exception;

class DnsOverHttpsProvider extends AbstractStorageProvider {

    private $endpoint = "https://dns.google.com/resolve";

    public function get_answer($question)
    {
        $answer = array();
        $domain = trim($question[0]['qname'], '.');
        $type = RecordTypeEnum::get_name($question[0]['qtype']);

        $records = $this->get_records($domain, $type);
        foreach($records as $record) {
            $answer[] = array(
                'name' => $record['name'],
                'class' => $question[0]['qclass'],
                'ttl' => $record['ttl'],
                'data' => array(
                    'type' => $record['type'],
                    'value' => $record['data']
                )
            );
        }

        return $answer;
    }

    private function get_records($domain, $type)
    {
        $result = array();

        $query_params = array(
            "name" => $domain,
            "type" => $type
        );
        $querystring = http_build_query($query_params, "", "&");

        $response = json_decode(file_get_contents($this->endpoint . "?" . $querystring));
        if (!isset($response->Answer)) {
            return $result;
        }

        foreach ($response->Answer as $answer) {
            $result[] = array(
                'name' => $answer->name,
                'type' => $answer->type,
                'ttl' => $answer->TTL,
                'data' => $answer->data
            );
        }

        return $result;
    }

}
