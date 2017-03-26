<?php

namespace App\Models;

use App\SIMPATHY\Utils;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Edge
 *
 * @property string                                                              $id
 * @property int                                                                 $start_id
 * @property int                                                                 $end_id
 * @property array                                                               $types
 * @property-read \App\Models\Node                                               $start
 * @property-read \App\Models\Node                                               $end
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pathway[] $pathways
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Edge whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Edge whereStartId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Edge whereEndId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Edge whereTypes($value)
 * @mixin \Eloquent
 * @property int                                                                 $organism_id
 * @property-read \App\Models\Organism                                           $organism
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Edge whereOrganismId($value)
 */
class Edge extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['start_id', 'end_id', 'types', 'organism_id'];

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'types' => 'array',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Start node relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function start()
    {
        return $this->belongsTo('App\Models\Node', 'start_id', 'id');
    }

    /**
     * Start node relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function end()
    {
        return $this->belongsTo('App\Models\Node', 'end_id', 'id');
    }

    /**
     * Organism of this edge
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organism()
    {
        return $this->belongsTo('App\Models\Organism', 'organism_id', 'id');
    }

    /**
     * Pathways with this edge
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function pathways()
    {
        return $this->belongsToMany('App\Models\Pathway', 'pathway_edges', 'edge_id', 'pathway_id');
    }

    /**
     * Compute Edge internal identifier
     *
     * @param string $start
     * @param string $end
     * @param string $organism
     *
     * @return string
     */
    public static function computeId(string $start, string $end, string $organism)
    {
        return Utils::makeKey($start, $end, $organism);
    }

    /**
     * Generate Id for this edge
     *
     * @return $this
     */
    public function generateId()
    {
        $this->id = self::computeId($this->start_id, $this->end_id, $this->organism_id);
        return $this;
    }

    /**
     * Save the model to the database.
     *
     * @param  array $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        $this->generateId();
        return parent::save($options);
    }


}
