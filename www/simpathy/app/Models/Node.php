<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Node
 *
 * @property int                                                                 $id
 * @property string                                                              $accession
 * @property string                                                              $name
 * @property string                                                              $type
 * @property array                                                               $aliases
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Edge[]    $ingoingEdges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Edge[]    $outgoingEdges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pathway[] $pathways
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Node whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Node whereAccession($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Node whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Node whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Node whereAliases($value)
 * @mixin \Eloquent
 * @property int                                                                 $organism_id
 * @property-read \App\Models\Organism                                           $organism
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Node whereOrganismId($value)
 */
class Node extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['accession', 'name', 'type', 'aliases'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'aliases' => 'array',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * References all ingoing edges from this node
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ingoingEdges()
    {
        return $this->hasMany('App\Models\Edge', 'end_id', 'id');
    }

    /**
     * References all outgoing edges from this node
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function outgoingEdges()
    {
        return $this->hasMany('App\Models\Edge', 'start_id', 'id');
    }

    /**
     * References all pathways with this edge
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function pathways()
    {
        return $this->belongsToMany('App\Models\Pathway', 'pathway_nodes', 'node_id', 'pathway_id');
    }

    /**
     * Returns the URL of this node for external references
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->type == 'mirna') {
            return 'http://www.mirbase.org/cgi-bin/query.pl?terms=' . $this->accession;
        } elseif ($this->type == 'gene') {
            return 'https://www.ncbi.nlm.nih.gov/gene/' . $this->accession;
        } else {
            return 'http://www.kegg.jp/dbget-bin/www_bget?' . $this->accession;
        }
    }

}
