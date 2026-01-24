<?php

namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WpTerm extends Model
{
    protected $connection = 'wordpress';

    protected $table = 'terms';

    protected $primaryKey = 'term_id';

    public $timestamps = false;

    /**
     * Get the taxonomy for this term.
     */
    public function taxonomy(): HasOne
    {
        return $this->hasOne(WpTermTaxonomy::class, 'term_id', 'term_id');
    }
}
