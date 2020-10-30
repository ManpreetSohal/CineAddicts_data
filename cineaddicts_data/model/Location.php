<?php

class Location
{
    const QUERY = "INSERT INTO locations (wiki_id, name, description, country_wiki_id) VALUES (:wiki_id, :name, :description, :country_wiki_id)";
    public $wiki_id;
    public $name;
    public $description;
    public $country_wiki_id;
}
