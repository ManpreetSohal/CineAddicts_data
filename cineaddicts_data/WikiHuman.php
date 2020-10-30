<?php 

class WikiHuman extends WikiBase {
    private $humanProperties = [
        'P18' => ['title' => 'image', 'multiple_values' => false],
        'P21' => ['title' => 'gender', 'multiple_values' => false],
        'P27' => ['title' => 'country_of_citizenship', 'multiple_values' => true],
        'P735' => ['title' => 'given_name', 'multiple_values' => false],
        'P734' => ['title' => 'last_name', 'multiple_values' => false],
        'P22' => ['title' => 'father', 'multiple_values' => false],
        'P25' => ['title' => 'mother', 'multiple_values' => false],
        'P569' => ['title' => 'date_of_birth', 'multiple_values' => false],
        'P19' => ['title' => 'place_of_birth', 'multiple_values' => false],
        'P106' => ['title' => 'occupation', 'multiple_values' => true],
        'P2002' => ['title' => 'twitter_username', 'multiple_values' => false],
        'P2003' => ['title' => 'instagram_username', 'multiple_values' => false],
        'P2013' => ['title' => 'facebook_username', 'multiple_values' => false],
        'P345' => ['title' => 'imdb_id', 'multiple_values' => false]
    ];


    public function __construct(){
        parent::__construct($this->humanProperties);
    }

    public function parseHumanData($rawData){

        $parsedHumanData['id'] = $this->wikiID;
        $parsedHumanData['full_name'] = $rawData['labels']['en']['value'];
        $parsedHumanData['descriptions'] = $rawData['descriptions']['en']['value'];

        $claimsData = $this->parseClaimsData($rawData['claims']);

        $imdbId = $claimsData['imdb_id'];    
        
        if(!empty($imdbId)){
            $apiResponse = Helper::makeRequest($imdbId, 1);
            if($apiResponse['success']){
                $tmdb_id = $apiResponse['response']['person_results'][0]['id'];
                $parsedHumanData['poster_path'] = $apiResponse['response']['person_results'][0]['profile_path'];

                $apiResponse = Helper::makeRequest($tmdb_id, 2);
                if($apiResponse['success']){
                    $parsedHumanData['biography'] = $apiResponse['response']['biography'];
                }
            }
        }
        
        return array_merge($parsedHumanData, $claimsData);
    }

    public function fetchHumanData($wiki_id){
        $parsedData = null;
        $apiResponse = Helper::makeRequest($wiki_id, 0);

        if($apiResponse['success']){
            $parsedData = $this->parseHumanData($apiResponse['response']['entities'][$wiki_id]);
        }
        
        return $parsedData;
    }
}

?>