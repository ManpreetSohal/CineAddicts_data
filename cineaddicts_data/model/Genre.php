<?php

class Genre
{
    const QUERY = "INSERT INTO movie_genres (wiki_id, genre) VALUES (:wiki_id, :genre)";
    public $wiki_id;
    public $genre;
}
