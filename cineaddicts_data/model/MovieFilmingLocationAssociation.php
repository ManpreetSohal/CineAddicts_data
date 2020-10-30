<?php

class MovieFilmingLocationAssociation
{
    const QUERY = "INSERT INTO movie_filming_location_association (movie_id, location_id) VALUES (:movie_id, :location_id)";
    public $movie_id;
    public $location_id;
}
