<?php

namespace App\Rules;

class SeoTitleLengthRule extends SeoMetadataRule
{
    public function __construct()
    {
        parent::__construct(70);
    }
}
