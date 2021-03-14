<?php
/**
 * PHENSIM: Phenotype Simulator
 * @version 2.0.0.2
 * @author  Salvatore Alaimo, Ph.D.
 */

namespace App\Models;

use App\Casts\ToArray;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Node extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'accession',
        'name',
        'aliases',
        'organism_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected array $appends = [
        'url',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected array $casts = [
        'aliases' => ToArray::class, // Convert Aliases in PHENSIM format to a PHP Array
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * Organism-to-nodes relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organism(): BelongsTo
    {
        return $this->belongsTo(Organism::class);
    }

    /**
     * Returns the URL of this node for external references
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        if (str_contains($this->accession, 'mir') || str_contains($this->accession, 'miR')) {
            return 'http://www.mirbase.org/cgi-bin/query.pl?terms=' . $this->accession;
        }
        if (str_starts_with($this->accession, 'chebi:')) {
            return 'https://www.ebi.ac.uk/chebi/searchId.do?chebiId=' . strtoupper($this->accession);
        }
        if (str_starts_with($this->accession, 'cpd:')) {
            return 'http://www.kegg.jp/dbget-bin/www_bget?' . $this->accession;
        }

        return 'https://www.ncbi.nlm.nih.gov/gene/' . $this->accession;
    }
}
