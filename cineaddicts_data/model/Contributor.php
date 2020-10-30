<?php

class Contributor
{
    const QUERY = "INSERT INTO contributors (wiki_id, first_name, last_name, stage_name, date_of_birth, gender_id, biography, location_id, twitter_path, instagram_path, facebook_path, poster_path) VALUES (:wiki_id, :first_name, :last_name, :stage_name, :date_of_birth, :gender_id, :biography, :location_id, :twitter_path, :instagram_path, :facebook_path, :poster_path)";
    public $wiki_id;
    public $first_name;
    public $last_name;
    public $stage_name;
    public $date_of_birth;
    public $location_id;
    public $gender_id;
    public $biography;
    public $instagram_path;
    public $facebook_path;
    public $twitter_path;
    public $poster_path;
}
