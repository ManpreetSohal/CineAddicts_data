<?php 

class WikiFilm extends WikiBase {
    private $movieProperties = [
        'P345' => ['title' => 'imdb_id', 'multiple_values' => false],
        'P462' => ['title' => 'color', 'multiple_values' => false],
        'P3212' => ['title' => 'isan', 'multiple_values' => false],
        'P840' => ['title' => 'narrative_location', 'multiple_values' => true],
        'P915' => ['title' => 'filming_location', 'multiple_values' => true],
        'P750' => ['title' => 'distributor', 'multiple_values' => true],
        'P58' => ['title' => 'screenwriter', 'multiple_values' => true],
        'P162' => ['title' => 'producer', 'multiple_values' => true],
        'P344' => ['title' => 'director_of_photography', 'multiple_values' => true],
        'P57' => ['title' => 'director', 'multiple_values' => true],
        'P364' => ['title' => 'original_language', 'multiple_values' => true],
        'P1476' => ['title' => 'title', 'multiple_values' => true],
        'P136' => ['title' => 'genre', 'multiple_values' => true],
        'P495' => ['title' => 'country_of_origin', 'multiple_values' => true],
        'P577' => ['title' => 'publication_date', 'multiple_values' => true],
        'P86' => ['title' => 'composer', 'multiple_values' => true],
        'P272' => ['title' => 'production_company', 'multiple_values' => true],
        'P2047' => ['title' => 'duration', 'multiple_values' => false],
        'P161' => ['title' => 'cast_member', 'multiple_values' => true],
        'P2130' => ['title' => 'cost', 'multiple_values' => false],
        'P2142' => ['title' => 'box_office', 'multiple_values' => false]
    ];

    public function __construct(){
        parent::__construct($this->movieProperties);
    }

    private function parseMovieData($rawData){

        $parsedMovieData['id'] = $this->wikiID;
        $parsedMovieData['title_en'] = $rawData['labels']['en']['value'];
        $parsedMovieData['descriptions'] = $rawData['descriptions']['en']['value'];

        $claimsData = $this->parseClaimsData($rawData['claims']);

        $imdbId = $claimsData['imdb_id'];    
        
        if(!empty($imdbId)){
            $apiResponse = Helper::makeRequest($imdbId, 1);
            if($apiResponse['success']){
                $parsedMovieData['poster_path'] = $apiResponse['response']['movie_results'][0]['poster_path'];
                $parsedMovieData['overview'] = $apiResponse['response']['movie_results'][0]['overview'];
            }
        }
        
        return array_merge($parsedMovieData, $claimsData);
    }

    public function fetchMovieData($wiki_id){
        $parsedData = null;
        $apiResponse = Helper::makeRequest($wiki_id, 0);

        if($apiResponse['success']){
            $parsedData = $this->parseMovieData($apiResponse['response']['entities'][$wiki_id]);
        }
        
        return $parsedData;
    }
}

?>