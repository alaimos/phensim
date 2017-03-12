<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Pathway
 *
 * @property int                                                              $id
 * @property string                                                           $accession
 * @property string                                                           $name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Node[] $nodes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Edge[] $edges
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Pathway whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Pathway whereAccession($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Pathway whereName($value)
 * @mixin \Eloquent
 */
class Pathway extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['accession', 'name'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Nodes in this pathway
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function nodes()
    {
        return $this->belongsToMany('App\Models\Node', 'pathway_nodes', 'pathway_id', 'node_id');
    }

    /**
     * Edges in this pathway
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function edges()
    {
        return $this->belongsToMany('App\Models\Edge', 'pathway_edges', 'pathway_id', 'edge_id');
    }
}
