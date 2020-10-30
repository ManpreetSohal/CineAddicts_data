<?php

class Language
{
    const QUERY = "INSERT INTO languages (wiki_id, language) VALUES (:wiki_id, :language)";
    public $wiki_id;
    public $language;
}
