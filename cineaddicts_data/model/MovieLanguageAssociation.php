<?php

class MovieLanguageAssociation
{
    const QUERY = "INSERT INTO movie_languages_association (movie_id, language_id) VALUES (:movie_id, :language_id)";
    public $movie_id;
    public $language_id;
}
