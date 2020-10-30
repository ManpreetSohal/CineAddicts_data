<?php 

class WikiBase {

    // https://www.wikidata.org/wiki/Wikidata:Database_reports/List_of_properties/all
    protected $properties = [
        'P31' => ['title' => 'instance_of', 'multiple_values' => false],
    ];

    protected $propertiesCodes = [];

    protected $wikiID = null;

    protected $wikiIDs = [];

    public function __construct($subProperties){
        
        $this->properties = array_merge($this->properties, $subProperties);

        $this->propertiesCodes = array_keys($this->properties);
    }

    protected function filterProperty($property){

        $properties = [];

        foreach($property as $item){

            if(array_key_exists('datavalue', $item['mainsnak'])){
                
                $itemData = $item['mainsnak'];
                $data = $itemValue = $item['mainsnak']['datavalue']['value'];
    
                $propertyCode = in_array($itemData['property'], $this->propertiesCodes ) ? $this->properties[$itemData['property']]['title'] : null;
    
                if($itemData['datatype'] == 'wikibase-item'){
                    $data = (string) $itemValue['id'];
                }
                else if($itemData['datatype'] == 'quantity'){
                    $data = (string) $itemValue['amount'];
                }
                else if($itemData['datatype'] == 'time'){
                    $data = (string) $itemValue['time'];
                }
    
                if(!is_null($propertyCode)){
                    if($this->properties[$itemData['property']]['multiple_values']){
                        $properties[$propertyCode][] = $data;
                    }
                    else{
                        $properties[$propertyCode] = $data;
                    }
                    
                }
                else{
                    //Helper::dataLogger('discarded_properties.log', $itemData['property'] . ' -> ' . print_r($data, true), $this->wikiID);
                }
            }
        }
        //print_r($properties);

        return $properties;
    }

    protected function parseClaimsData($rawClaims){
        
        $parsedClaimsData = [];

        foreach ($rawClaims as $property) {

            $filteredData = $this->filterProperty($property);

            if(!empty($filteredData)){

                foreach ($filteredData as $key => $value) {
                    $parsedClaimsData[$key] = $value;
                }
            }
        }
        
        return $parsedClaimsData;
    }
}

?>