<?php 
require_once "database/database.php";
require_once "model/Movie.php";
require_once "model/Genre.php";
require_once "model/Language.php";
require_once "model/Country.php";
require_once "model/Location.php";
require_once "model/Contributor.php";
require_once "model/ContributorRole.php";
require_once "model/CompanyRole.php";
require_once "model/Gender.php";
require_once "model/MovieGenreAssociation.php";
require_once "model/ContributorCountryAssociation.php";
require_once "model/MovieLanguageAssociation.php";
require_once "model/MovieNarrativeLocationAssociation.php";
require_once "model/MovieFilmingLocationAssociation.php";
require_once "model/MovieContributorAssociation.php";
require_once "model/MovieCompanyAssociation.php";
require_once "model/MovieCountryAssociation.php";
require_once "model/Company.php";
require_once "model/ListType.php";
require_once "model/UserRole.php";


class WikiDataFetcher {
    const SCREEN_WRITER_ROLE_ID = 1;
    const CAST_MEMBER_ROLE_ID = 2;
    const DIRECTOR_ROLE_ID = 3;
    const PRODUCER_ROLE_ID = 4;
    const DISTRIBUTOR_ROLE_ID = 1;
    const PRODUCTION_COMPANY_ROLE_ID = 2;
    
    private $db_conn  = null;
    private $film_obj = null;
    private $human_obj = null;
    private $wiki_ids = null;

    public function __construct(){
        $this->db_conn = new Database();
        $this->film_obj = new WikiFilm();
        $this->human_obj = new WikiHuman();
    }

    public function fillRoleTables(){
        $contributor_role_screen_writer = new ContributorRole();
        $contributor_role_screen_writer->wiki_property = "P58";
        $contributor_role_screen_writer->contributor_role = "Screenwriter";

        $contributor_role_cast_member = new ContributorRole();
        $contributor_role_cast_member->wiki_property = "P161";
        $contributor_role_cast_member->contributor_role = "Cast member";

        $contributor_role_director = new ContributorRole();
        $contributor_role_director->wiki_property = "P57";
        $contributor_role_director->contributor_role = "Director";

        $contributor_role_producer = new ContributorRole();
        $contributor_role_producer->wiki_property = "P162";
        $contributor_role_producer->contributor_role = "Producer";

        $company_role_distributor = new CompanyRole();
        $company_role_distributor->wiki_property = "P750";
        $company_role_distributor->company_role = "Distributor";

        $company_role_production_company = new CompanyRole();
        $company_role_production_company->wiki_property = "P272";
        $company_role_production_company->company_role = "Production company";

        $list_type_watch = new ListType();
        $list_type_watch->id = 1;
        $list_type_watch->type = "Watch List";

        $list_type_seen = new ListType();
        $list_type_seen->id = 2;
        $list_type_seen->type = "Seen List";

        $list_type_other = new ListType();
        $list_type_other->id = 3;
        $list_type_other->type = "Other";

        $user_role = new UserRole();
        $user_role->id = 1;
        $user_role->role = "user";
        
        $admin_role = new UserRole();
        $admin_role->id = 2;
        $admin_role->role = "admin";
        
        $roles = array($contributor_role_screen_writer, $contributor_role_cast_member, $contributor_role_director, $contributor_role_producer, $company_role_distributor, $company_role_production_company, $list_type_watch, $list_type_seen, $list_type_other, $user_role, $admin_role);
        
        foreach($roles as $role){
            $this->db_conn->insert($role);
        }
    }

    private function setWikiIDs($filename){
        try {
            $this->wiki_ids = Helper::parseCSV($filename);
        }
        catch (Exception $e) {
            Helper::dataLogger('errors.log', __METHOD__ . " : {$e->getMessage()}", $this->wikiID);
        }
    }

    public function fetch($filename){
        if(file_exists($filename)){
            $this->setWikiIDs($filename);

            if(!empty($this->wiki_ids)){
                foreach ($this->wiki_ids as $wiki_id) {
                    $id = $wiki_id['id'];
                    $this->addMovie($id);
                }
            }      
        }
        else{
            throw new Exception("ERROR: '{$filename}' does not exist in the root directory.");
        }
    }
   

    public function addMovie($movie_wiki_id){
        $id = $this->db_conn->getWikiRecordId('movies', $movie_wiki_id);
        if($id == null){
                $movieData = $this->film_obj->fetchMovieData($movie_wiki_id);

                $movie = new Movie();
                $movie->title = $movieData['title_en'];
                $movie->wiki_id = $movie_wiki_id;
                $movie->release_date = DateTime::createFromFormat('Y',substr($movieData['publication_date'][0],1,4))->format('Y-m-d');;               
                $movie->runtime = intval($movieData['duration']);
                $movie->poster_image_path = $movieData['poster_path'];
                $movie->budget = round((($movieData['cost']) / 1000000), 1);
                $movie->box_office = round((($movieData['box_office'])) / 1000000, 1);
                $movie->synopsis = $movieData['overview'];
                
                $movie_id = $this->db_conn->insert($movie);
    
                $movie_genres = $movieData['genre']; 
                if(!empty($movie_genres)){
                    foreach($movie_genres as $movie_genre_wiki_id){
                       $movie_genre_id =  $this->addGenre($movie_genre_wiki_id);
                       $this->createMovieGenreAssociation($movie_id, $movie_genre_id);
                    }
                }   

                $original_languages = $movieData['original_language'];
                if(!empty($original_languages)){
                    foreach($original_languages as $language_wiki_id){
                        $language_id = $this->addLanguage($language_wiki_id);
                        $this->createMovieLanguageAssociation($movie_id, $language_id);
                    }
                }

                $countries = $movieData['country_of_origin']; 
                if(!empty($countries)){    
                    foreach($countries as $country_wiki_id){
                        $country_id = $this->addCountry($country_wiki_id);
                        $this->createMovieCountryAssociation($movie_id, $country_id);    
                    }
                }
                
                $screen_writers = $movieData['screenwriter'];
                if(!empty($screen_writers)){
                    foreach($screen_writers as $screen_writer_wiki_id){
                        $contributor_id = $this->addContributor($screen_writer_wiki_id);
                        $this->createMovieContributorAssociation($movie_id, $contributor_id, $this::SCREEN_WRITER_ROLE_ID);
                    }       
                }

                $cast_members = $movieData['cast_member'];
                if(!empty($cast_members)){
                    foreach($cast_members as $cast_member_wiki_id){
                        $contributor_id = $this->addContributor($cast_member_wiki_id);
                        $this->createMovieContributorAssociation($movie_id, $contributor_id, $this::CAST_MEMBER_ROLE_ID);
                    }       
                }

                $directors = $movieData['director'];
                if(!empty($directors)){
                    foreach($directors as $director_wiki_id){
                        $contributor_id = $this->addContributor($director_wiki_id);
                        $this->createMovieContributorAssociation($movie_id, $contributor_id, $this::DIRECTOR_ROLE_ID);
                    }       
                }

                $producers = $movieData['producer'];
                if(!empty($producers)){
                    foreach($producers as $producer_wiki_id){
                        $contributor_id = $this->addContributor($producer_wiki_id);
                        $this->createMovieContributorAssociation($movie_id, $contributor_id, $this::PRODUCER_ROLE_ID);
                    }

                }

                $distributors = $movieData['distributor'];
                if(!empty($distributors)){
                    foreach($distributors as $distributor_wiki_id){
                        $company_id = $this->addCompany($distributor_wiki_id);
                        $this->createMovieCompanyAssociation($movie_id, $company_id, $this::DISTRIBUTOR_ROLE_ID);    
                    }       
                }

                $production_companies = $movieData['production_company'];
                if(!empty($production_companies)){
                    foreach($production_companies as $production_company_wiki_id){
                        $company_id = $this->addCompany($production_company_wiki_id);
                        $this->createMovieCompanyAssociation($movie_id, $company_id, $this::PRODUCTION_COMPANY_ROLE_ID);                 }       
                }

                $filming_locations = $movieData['filming_location'];
                if(!empty($filming_locations)){
                    foreach($filming_locations as $filming_location_wiki_id){
                        $location_id = $this->addLocation($filming_location_wiki_id);
                        $this->createMovieFilmingLocationAssociation($movie_id, $location_id);
                    }       
                }
                 
                $narrative_locations = $movieData['narrative_location'];
                if(!empty($narrative_locations)){
                    foreach($narrative_locations as $narrative_location_wiki_id){
                        $location_id = $this->addLocation($narrative_location_wiki_id);
                        $this->createMovieNarrativeLocationAssociation($movie_id, $location_id);
                    }       
                }
            }
            else{
                print_r("Wiki item with id ". $movie_wiki_id . "already exists in the database");
            }
    }

    public function addGenre($movie_genre_wiki_id){
        $id = $this->db_conn->getWikiRecordId('movie_genres', $movie_genre_wiki_id);
        
        if($id == null){
            $apiResponse = Helper::makeRequest($movie_genre_wiki_id, 0);
            $genre = new Genre();
            $genre->wiki_id = $movie_genre_wiki_id;
            $genre->genre = $apiResponse['response']['entities'][$movie_genre_wiki_id]['labels']['en']['value'];
            return $this->db_conn->insert($genre);
        }
        else{
            return $id;
        }
    }

    public function addLanguage($language_wiki_id){
        $id = $this->db_conn->getWikiRecordId('languages', $language_wiki_id);

        if($id == null){
            $apiResponse = Helper::makeRequest($language_wiki_id, 0);
            $language = new Language();
            $language->wiki_id = $language_wiki_id;
            $language->language = $apiResponse['response']['entities'][$language_wiki_id]['labels']['en']['value'];
            return $this->db_conn->insert($language);
        }
        else{
            return $id;
        }
    }

    public function addCompany($company_wiki_id){
        $id = $this->db_conn->getWikiRecordId('companies', $company_wiki_id);

        if($id == null){
            $apiResponse = Helper::makeRequest($company_wiki_id, 0);
            $company = new Company();
            $company->wiki_id = $company_wiki_id;
            $company->company = $apiResponse['response']['entities'][$company_wiki_id]['labels']['en']['value'];
            return $this->db_conn->insert($company);
        }
        else{
            return $id;
        }
    }

    public function addCountry($country_wiki_id){
        $id = $this->db_conn->getWikiRecordId('countries', $country_wiki_id);

        if($id == null){
            $apiResponse = Helper::makeRequest($country_wiki_id, 0);
            $country = new Country();
            $country->wiki_id = $country_wiki_id;
            $country->country = $apiResponse['response']['entities'][$country_wiki_id]['labels']['en']['value'];
            return $this->db_conn->insert($country);
        }
        else{
            return $id;
        }
    }

    public function addLocation($location_wiki_id){
        $id = $this->db_conn->getWikiRecordId('locations', $location_wiki_id);

        if($id == null){
            $loc_data = Helper::makeRequest($location_wiki_id, 0);
            $location = new Location();
            $location->wiki_id = $location_wiki_id;
            $location->name = $loc_data['response']['entities'][$location_wiki_id]['labels']['en']['value'];
            $location->description = $loc_data['response']['entities'][$location_wiki_id]['descriptions']['en']['value'];
            $location->country_wiki_id = end($loc_data['response']['entities'][$location_wiki_id]['claims']['P17'])['mainsnak']['datavalue']['value']['id'];
            if($location->country_wiki_id != null){
                $this->addCountry($location->country_wiki_id);
            }
            return $this->db_conn->insert($location);         
        }
        else{
            return $id;
        }
    }

    public function addGender($gender_wiki_id){
        $id = $this->db_conn->getWikiRecordId('genders', $gender_wiki_id);

        if($id == null){
            $gender_data = Helper::makeRequest($gender_wiki_id, 0);
            $gender = new Gender();
            $gender->wiki_id = $gender_wiki_id;
            $gender->gender = $gender_data['response']['entities'][$gender_wiki_id]['labels']['en']['value'];
            return $this->db_conn->insert($gender);         
        }
        else{
            return $id;
        }
    }

    public function addContributor($contributor_wiki_id){
        $id = $this->db_conn->getWikiRecordId('contributors', $contributor_wiki_id);

        if($id == null){
            $contributor_data = $this->human_obj->fetchHumanData($contributor_wiki_id);

            $contributor = new Contributor();
            $contributor->wiki_id = $contributor_wiki_id;

            $given_name_wiki_id = $contributor_data['given_name'];
            $last_name_wiki_id = $contributor_data['last_name'];
            
            $dob = new DateTime($contributor_data['date_of_birth']);
            $contributor->date_of_birth = $dob->format('Y-m-d');  
            
            if(!empty($given_name_wiki_id)){    
                $contributor->first_name = Helper::makeRequest($given_name_wiki_id, 0)['response']['entities'][$given_name_wiki_id]['labels']['en']['value'];
            }

            if(!empty($last_name_wiki_id)){
                $contributor->last_name = Helper::makeRequest($last_name_wiki_id, 0)['response']['entities'][$last_name_wiki_id]['labels']['en']['value'];
            }
            $contributor->stage_name = $contributor_data['full_name'];

            $pob_wiki_id = $contributor_data['place_of_birth'];
            if(isset($pob_wiki_id)){
                $location_id = $this->addLocation($pob_wiki_id);
                $contributor->location_id = $location_id;
            }

            $gender_wiki_id = $contributor_data['gender'];
            if(isset($gender_wiki_id)){
                $gender_id = $this->addGender($gender_wiki_id);
                $contributor->gender_id = $gender_id;
            }

            $contributor->biography = $contributor_data['biography']; 

            if(!empty($contributor_data['facebook_username'])){
                $contributor->facebook_path = $contributor_data['facebook_username'];
            }

            if(!empty($contributor_data['twitter_username'])){
                $contributor->twitter_path = $contributor_data['twitter_username'];
            }

            if(!empty($contributor_data['instagram_username'])){
               $contributor->instagram_path = $contributor_data['instagram_username'];
            }

            $contributor->poster_path = $contributor_data['poster_path'];
            
            $contributor_id =  $this->db_conn->insert($contributor);

            $countries_of_citizenship = $contributor_data['country_of_citizenship'];
            if(!empty($countries_of_citizenship)){
                foreach($countries_of_citizenship as $country_wiki_id){
                    $contributor_country_association = new ContributorCountryAssociation();
                    $contributor_country_association->contributor_id = $contributor_id;
                    $contributor_country_association->country_id = $this->addCountry($country_wiki_id);
                    $this->db_conn->insert($contributor_country_association); 
                }
            }
            return $contributor_id;
        }
        else{
            return $id;
        }
     }
     
     public function createMovieGenreAssociation($movie_id, $genre_id){
        $movie_genre_association = new MovieGenreAssociation();
        $movie_genre_association->movie_id = $movie_id;
        $movie_genre_association->genre_id = $genre_id;
        $this->db_conn->insert($movie_genre_association);
     }

     public function createMovieLanguageAssociation($movie_id, $language_id){
         $movie_language_association = new MovieLanguageAssociation();
         $movie_language_association->movie_id = $movie_id;
         $movie_language_association->language_id = $language_id;
         $this->db_conn->insert($movie_language_association);
     }

     public function createMovieContributorAssociation($movie_id, $contributor_id, $role_id){
        $movie_contributor_association  = new MovieContributorAssociation();;
        $movie_contributor_association->movie_id = $movie_id;
        $movie_contributor_association->contributor_id = $contributor_id;
        $movie_contributor_association->role_id = $role_id;
        $this->db_conn->insert($movie_contributor_association);
    }

    public function createMovieCompanyAssociation($movie_id, $company_id, $role_id){
        $movie_company_association  = new MovieCompanyAssociation();
        $movie_company_association->movie_id = $movie_id;
        $movie_company_association->company_id = $company_id;
        $movie_company_association->role_id = $role_id;
        $this->db_conn->insert($movie_company_association);
    }
 
    public function createMovieNarrativeLocationAssociation($movie_id, $location_id){
        $movie_narrative_location_association = new MovieNarrativeLocationAssociation();
        $movie_narrative_location_association->movie_id = $movie_id;
        $movie_narrative_location_association->location_id = $location_id;
        $this->db_conn->insert($movie_narrative_location_association);
    }

    public function createMovieFilmingLocationAssociation($movie_id, $location_id){
        $movie_filming_location_association = new MovieFilmingLocationAssociation();
        $movie_filming_location_association->movie_id = $movie_id;
        $movie_filming_location_association->location_id = $location_id;
        $this->db_conn->insert($movie_filming_location_association);
    }

    public function createMovieCountryAssociation($movie_id, $country_id){
        $movie_country_association = new MovieCountryAssociation();
        $movie_country_association->movie_id = $movie_id;
        $movie_country_association->country_id = $country_id;
        $this->db_conn->insert($movie_country_association);
    }
}
?>