<?php

class Country
{
    const QUERY = "INSERT INTO countries (wiki_id, country) VALUES (:wiki_id, :country)";
    public $wiki_id;
    public $country;
}
