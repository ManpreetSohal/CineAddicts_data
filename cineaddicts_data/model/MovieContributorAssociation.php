<?php

class MovieContributorAssociation
{
    const QUERY = "INSERT INTO movies_contributors_association (movie_id, contributor_id, role_id) VALUES (:movie_id, :contributor_id, :role_id)";
    public $movie_id;
    public $contributor_id;
    public $role_id;
}
