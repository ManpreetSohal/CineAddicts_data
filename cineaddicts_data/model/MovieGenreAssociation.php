<?php

class MovieGenreAssociation
{
    const QUERY = "INSERT INTO movies_genres_association (movie_id, genre_id) VALUES (:movie_id, :genre_id)";
    public $movie_id;
    public $genre_id;
}
