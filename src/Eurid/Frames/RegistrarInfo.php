<?php

namespace AgileGeeks\EPP\Eurid\Frames;

use AgileGeeks\EPP\Eurid\Frames\Command;

require_once(__DIR__ . '/Command.php');

class RegistrarInfo extends Command
{
    const   FINANCE     = "FINANCE";
    const   HITPOINTS   = "HITPOINTS";
    const   LIMITS      = "LIMITS";

    const   SCHEMA  = array(
        self::FINANCE   => 'http://www.eurid.eu/xml/epp/registrarFinance-1.0',
        self::HITPOINTS => 'http://www.eurid.eu/xml/epp/registrarHitPoints-1.0',
        self::LIMITS    => 'http://www.eurid.eu/xml/epp/registrationLimit-1.1',
    );

    const TEMPLATE = <<<XML
    <command>
        <info>
          <registrar:info xmlns:registrar="%s"/>
        </info>
        <clTRID>%s</clTRID>
    </command>
XML;

    function __construct($type = self::FINANCE)
    {
        $this->xml = sprintf(
            self::TEMPLATE,
            self::SCHEMA[$type],
            $this->clTRID()
        );
    }

    function getResult($dom)
    {
        parent::getResult($dom);

        $result = new \stdClass();

        // try all defined schemas
        foreach(self::SCHEMA as $schema => $uri) {
            $infData_node = $dom->getElementsByTagNameNS($uri, 'infData');
            if ($infData_node && $infData_node->length) {
                $infData_node = $infData_node->item(0);

                $result->type = $schema;
                // all the child nodes
                foreach($infData_node->childNodes as $child) {
                    if ($child->nodeType !== XML_ELEMENT_NODE) {
                        continue;
                    }
                    $result->{$child->localName} = $child->textContent;
                }
            }
        }
        return $result;
    }
}
