<?php

class ContributorCountryAssociation
{
    const QUERY = "INSERT INTO contributor_country_association (contributor_id, country_id) VALUES (:contributor_id, :country_id)"; 
    public $contributor_id;
    public $country_id;
}
