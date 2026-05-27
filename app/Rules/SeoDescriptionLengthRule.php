<?php

namespace App\Rules;

class SeoDescriptionLengthRule extends SeoMetadataRule
{
    public function __construct()
    {
        parent::__construct(180);
    }
}
