<?php

class MovieCountryAssociation
{
    const QUERY = "INSERT INTO movie_country_association (movie_id, country_id) VALUES (:movie_id, :country_id)";
    public $movie_id;
    public $country_id;
}
