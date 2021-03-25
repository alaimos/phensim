<?php
/**
 * PHENSIM: Phenotype Simulator
 * @version 2.0.0.2
 * @author  Salvatore Alaimo, Ph.D.
 */

namespace App\Models;

use App\Jobs\SimulationJob;
use App\PHENSIM\Reader;
use App\PHENSIM\Utils;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Simulation extends Model
{

    //region Constants associated with the Simulation State

    /**
     * Constants used to represent status values
     */
    public const READY      = 0;
    public const QUEUED     = 1;
    public const PROCESSING = 2;
    public const COMPLETED  = 3;
    public const FAILED     = 4;

    /**
     * An array of allowed values for the status field
     */
    public const VALID_STATES = [self::READY, self::QUEUED, self::PROCESSING, self::COMPLETED, self::FAILED];

    /**
     * An array mapping states to their names
     */
    public const STATE_NAMES = [
        self::READY      => 'Ready',
        self::QUEUED     => 'Queued',
        self::PROCESSING => 'Processing',
        self::COMPLETED  => 'Completed',
        self::FAILED     => 'Failed',
    ];

    /**
     * An array mapping states to human readable strings
     */
    public const HUMAN_READABLE_STATES = [
        self::READY      => 'Ready',
        self::QUEUED     => 'Queued',
        self::PROCESSING => 'Processing',
        self::COMPLETED  => 'Completed',
        self::FAILED     => 'Failed',
    ];

    //endregion

    //region Laravel Eloquent Stuff

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'status',
        'input_parameters_file',
        'enrichment_database_file',
        'node_types_file',
        'edge_types_file',
        'edge_subtypes_file',
        'non_expressed_nodes_file',
        'parameters',
        'output_file',
        'pathway_output_file',
        'nodes_output_file',
        'sbml_output_file',
        'sif_output_file',
        'logs',
        'public',
        'public_key',
        'user_id',
        'organism_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'parameters' => 'array',
        'public'     => 'boolean',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'public'     => false,
        'public_key' => null,
        'logs'       => '',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'readable_status',
    ];

    /**
     * Scope a query to only include visible simulations to an user.
     * If the user is an admin all simulations are visible.
     * If the user is not logged in no simulations are visible.
     * In all other cases shows only the owned simulations
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Models\User|null  $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible(Builder $query, ?User $user = null): Builder
    {
        if ($user === null) {
            $user = Auth::user();
        }
        if ($user === null) {
            return $query->whereRaw('1 <> 1');
        }
        if ($user->is_admin) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    /**
     * User-to-simulation relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Simulation-to-organism relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organism(): BelongsTo
    {
        return $this->belongsTo(Organism::class);
    }

    /**
     * Set the log attribute.
     *
     * @param  string  $value
     */
    public function setLogsAttribute(string $value): void
    {
        $aLines = explode("\n", $value);
        $value = implode(
            "\n",
            array_map(
                static function ($line) {
                    $line = preg_replace('/\033\[([0-9;]+)m/i', '', $line);
                    if (!str_contains($line, "\r")) {
                        return $line;
                    }
                    $arr = array_filter(explode("\r", $line));
                    $n = count($arr);
                    if ($n > 0) {
                        return last($arr);
                    }

                    return '';
                },
                $aLines
            )
        );
        $this->attributes['logs'] = $value;
    }

    /**
     * Checks for a valid state and sets the value in this model.
     * If an invalid state is provided, it will be replaced with a FAILED state.
     *
     * @param  int  $value
     *
     * @return void
     */
    public function setStatusAttribute(int $value): void
    {
        if (!in_array($value, self::VALID_STATES)) {
            $value = self::FAILED;
        }
        $this->attributes['status'] = $value;
    }

    /**
     * Returns the human readable status of this job
     *
     * @return string
     */
    public function getReadableStatusAttribute(): string
    {
        return self::HUMAN_READABLE_STATES[$this->status];
    }

    /**
     * Returns the value of a file attribute
     *
     * @param  string  $attribute
     *
     * @return string|null
     * @throws \App\Exceptions\FileSystemException
     */
    private function getFileAttr(string $attribute): ?string
    {
        if (!isset($this->attributes[$attribute]) || !$this->attributes[$attribute]) {
            return null;
        }

        return $this->jobFile($this->attributes[$attribute]);
    }

    /**
     * Set the value of a file attribute in this model
     *
     * @param  string  $attribute
     * @param  string|null  $value
     *
     * @return void
     */
    private function setFileAttr(string $attribute, ?string $value): void
    {
        if ($value === null || !file_exists($value)) {
            $this->attributes[$attribute] = null;
        } else {
            $this->attributes[$attribute] = basename($value);
        }
    }

    /**
     * Return the input parameters file path of this simulation. If no file exists null will be returned.
     *
     * @return string|null
     * @throws \App\Exceptions\FileSystemException
     */
    public function getInputParametersFileAttribute(): ?string
    {
        return $this->getFileAttr('input_parameters_file');
    }

    /**
     * Set the input parameters filename of this simulation. If the file does not exist null will be stored
     *
     * @param  string|null  $value
     *
     * @return void
     */
    public function setInputParametersFileAttribute(?string $value): void
    {
        $this->setFileAttr('input_parameters_file', $value);
    }

    /**
     * Return the enrichment database file path of this simulation. If no file exists null will be returned.
     *
     * @return string|null
     * @throws \App\Exceptions\FileSystemException
     */
    public function getEnrichmentDatabaseFileAttribute(): ?string
    {
        return $this->getFileAttr('enrichment_database_file');
    }

    /**
     * Set the enrichment database filename of this simulation. If the file does not exist null will be stored
     *
     * @param  string|null  $value
     *
     * @return void
     */
    public function setEnrichmentDatabaseFileAttribute(?string $value): void
    {
        $this->setFileAttr('enrichment_database_file', $value);
    }

    /**
     * Return the node types file path of this simulation. If no file exists null will be returned.
     *
     * @return string|null
     * @throws \App\Exceptions\FileSystemException
     */
    public function getNodeTypesFileAttribute(): ?string
    {
        return $this->getFileAttr('node_types_file');
    }

    /**
     * Set the node types filename of this simulation. If the file does not exist null will be stored
     *
     * @param  string|null  $value
     *
     * @return void
     */
    public function setNodeTypesFileAttribute(?string $value): void
    {
        $this->setFileAttr('node_types_file', $value);
    }

    /**
     * Return the edge types file path of this simulation. If no file exists null will be returned.
     *
     * @return string|null
     * @throws \App\Exceptions\FileSystemException
     */
    public function getEdgeTypesFileAttribute(): ?string
    {
        return $this->getFileAttr('edge_types_file');
    }

    /**
     * Set the edge types filename of this simulation. If the file does not exist null will be stored
     *
     * @param  string|null  $value
     *
     * @return void
     */
    public function setEdgeTypesFileAttribute(?string $value): void
    {
        $this->setFileAttr('edge_types_file', $value);
    }

    /**
     * Return the edge subtypes file path of this simulation. If no file exists null will be returned.
     *
     * @return string|null
     * @throws \App\Exceptions\FileSystemException
     */
    public function getEdgeSubtypesFileAttribute(): ?string
    {
        return $this->getFileAttr('edge_subtypes_file');
    }

    /**
     * Set the edge subtypes filename of this simulation. If the file does not exist null will be stored
     *
     * @param  string|null  $value
     *
     * @return void
     */
    public function setEdgeSubtypesFileAttribute(?string $value): void
    {
        $this->setFileAttr('edge_subtypes_file', $value);
    }

    /**
     * Return the non-expressed nodes file path of this simulation. If no file exists null will be returned.
     *
     * @return string|null
     * @throws \App\Exceptions\FileSystemException
     */
    public function getNonExpressedNodesFileAttribute(): ?string
    {
        return $this->getFileAttr('non_expressed_nodes_file');
    }

    /**
     * Set the non-expressed nodes filename of this simulation. If the file does not exist null will be stored
     *
     * @param  string|null  $value
     *
     * @return void
     */
    public function setNonExpressedNodesFileAttribute(?string $value): void
    {
        $this->setFileAttr('non_expressed_nodes_file', $value);
    }

    /**
     * Return the output file path of this simulation. If no file exists null will be returned.
     *
     * @return string|null
     * @throws \App\Exceptions\FileSystemException
     */
    public function getOutputFileAttribute(): ?string
    {
        return $this->getFileAttr('output_file');
    }

    /**
     * Set the output filename of this simulation. If the file does not exist null will be stored
     *
     * @param  string|null  $value
     *
     * @return void
     */
    public function setOutputFileAttribute(?string $value): void
    {
        $this->setFileAttr('output_file', $value);
    }

    /**
     * Return the pathway matrix file path of this simulation. If no file exists null will be returned.
     *
     * @return string|null
     * @throws \App\Exceptions\FileSystemException
     */
    public function getPathwayOutputFileAttribute(): ?string
    {
        return $this->getFileAttr('pathway_output_file');
    }

    /**
     * Set the pathway matrix filename of this simulation. If the file does not exist null will be stored
     *
     * @param  string|null  $value
     *
     * @return void
     */
    public function setPathwayOutputFileAttribute(?string $value): void
    {
        $this->setFileAttr('pathway_output_file', $value);
    }

    /**
     * Return the nodes matrix file path of this simulation. If no file exists null will be returned.
     *
     * @return string|null
     * @throws \App\Exceptions\FileSystemException
     */
    public function getNodesOutputFileAttribute(): ?string
    {
        return $this->getFileAttr('nodes_output_file');
    }

    /**
     * Set the nodes matrix filename of this simulation. If the file does not exist null will be stored
     *
     * @param  string|null  $value
     *
     * @return void
     */
    public function setNodesOutputFileAttribute(?string $value): void
    {
        $this->setFileAttr('nodes_output_file', $value);
    }

    /**
     * Return the SBML file path of this simulation. If no file exists null will be returned.
     *
     * @return string|null
     * @throws \App\Exceptions\FileSystemException
     */
    public function getSbmlOutputFileAttribute(): ?string
    {
        return $this->getFileAttr('sbml_output_file');
    }

    /**
     * Set the SBML filename of this simulation. If the file does not exist null will be stored
     *
     * @param  string|null  $value
     *
     * @return void
     */
    public function setSbmlOutputFileAttribute(?string $value): void
    {
        $this->setFileAttr('sbml_output_file', $value);
    }

    /**
     * Return the SIF file path of this simulation. If no file exists null will be returned.
     *
     * @return string|null
     * @throws \App\Exceptions\FileSystemException
     */
    public function getSifOutputFileAttribute(): ?string
    {
        return $this->getFileAttr('sif_output_file');
    }

    /**
     * Set the SIF filename of this simulation. If the file does not exist null will be stored
     *
     * @param  string|null  $value
     *
     * @return void
     */
    public function setSifOutputFileAttribute(?string $value): void
    {
        $this->setFileAttr('sif_output_file', $value);
    }
    //endregion

    //region Visibility Management

    /**
     * Generate a public key for this simulation
     *
     * @return string
     */
    private function makePublicKey(): string
    {
        $user = $this->user ? Str::slug($this->user->email) . '_' : 'anonymous_';

        return uniqid($user, true);
    }

    /**
     * Make this simulation publicly available and returns the generated key
     *
     * @return string
     */
    public function makePublic(): string
    {
        if (!$this->public) {
            $this->update(
                [
                    'public'     => true,
                    'public_key' => $this->makePublicKey(),
                ]
            );
        }

        return $this->public_key;
    }

    /**
     * Makes this simulation private. The generated key is also removed!
     */
    public function makePrivate(): void
    {
        if ($this->public) {
            $this->update(
                [
                    'public'     => false,
                    'public_key' => null,
                ]
            );
        }
    }

    //endregion

    //region Parameters and Data Methods

    /**
     * Append text to the log
     *
     * @param  string  $text
     * @param  boolean  $appendNewLine
     * @param  boolean  $commit
     *
     * @return $this
     */
    public function appendLog(string $text, bool $appendNewLine = true, bool $commit = true): self
    {
        if ($appendNewLine) {
            $text .= "\n";
        }
        $this->logs .= $text;
        if ($commit) {
            $this->save();
        }

        return $this;
    }

    /**
     * Get the value of a parameter for this job
     *
     * @param  string  $parameter
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function getParameter(string $parameter, $default = null): mixed
    {
        return $this->parameters[$parameter] ?? $default;
    }

    /**
     * Set the value of a parameter for this job
     *
     * @param  string  $parameter
     * @param  mixed  $value
     *
     * @return $this
     */
    public function setParameter(string $parameter, mixed $value): self
    {
        $tmp = $this->parameters;
        $tmp[$parameter] = $value;
        $this->parameters = $tmp;

        return $this;
    }

    //endregion

    //region Job Directory Stuff

    /**
     * Returns the relative path of the folder where all simulation files will be stored.
     *
     * @return string
     */
    public function jobDirectoryRelative(): string
    {
        return 'jobs/' . $this->id;
    }

    /**
     * Returns the absolute path of the folder where all files of this simulation will be stored
     * If the directory does not exist it will be created.
     *
     * @return string
     * @throws \App\Exceptions\FileSystemException
     */
    public function jobDirectory(): string
    {
        return Utils::getStorageDirectory($this->jobDirectoryRelative());
    }

    /**
     * Returns the absolute path of a file in the job storage directory
     *
     * @param  string  $filename
     *
     * @return string
     * @throws \App\Exceptions\FileSystemException
     */
    public function jobFile(string $filename): string
    {
        return $this->jobDirectory() . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Delete the job directory
     *
     * @return bool
     * @throws \App\Exceptions\FileSystemException
     */
    public function deleteJobDirectory(): bool
    {
        return Utils::delete($this->jobDirectory());
    }

    //endregion

    /**
     * Checks if this job is in a state suitable for deletion
     *
     * @return bool
     */
    public function canBeDeleted(): bool
    {
        return !in_array($this->status, [self::QUEUED, self::PROCESSING], true);
    }

    /**
     * Checks if this job might have any logs to display
     *
     * @return bool
     */
    public function hasLogs(): bool
    {
        return !in_array($this->status, [self::READY, self::QUEUED], true);
    }

    /**
     * Checks if this job can be submitted
     *
     * @return bool
     */
    public function isReady(): bool
    {
        return $this->status === self::READY;
    }

    /**
     * Checks if this job is processing
     *
     * @return bool
     */
    public function isProcessing(): bool
    {
        return $this->status === self::PROCESSING;
    }

    /**
     * Checks if this job has been completed
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === self::COMPLETED;
    }

    /**
     * Checks if this job has been completed
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === self::FAILED;
    }

    /**
     * Submit an analysis job to the queue
     *
     * @return void
     */
    public function submit(): void
    {
        $this->update(['status' => self::QUEUED]);
        SimulationJob::dispatch($this);
    }

    /**
     * Resubmit a failed analysis job
     */
    public function reSubmit(): void
    {
        Reader::cleanupCache($this->output_file);
        if ($this->output_file !== null && file_exists($this->output_file)) {
            @unlink($this->output_file);
            $this->output_file = null;
        }
        if ($this->nodes_output_file !== null && file_exists($this->nodes_output_file)) {
            @unlink($this->nodes_output_file);
            $this->nodes_output_file = null;
        }
        if ($this->pathway_output_file !== null && file_exists($this->pathway_output_file)) {
            @unlink($this->pathway_output_file);
            $this->pathway_output_file = null;
        }
        if ($this->sbml_output_file !== null && file_exists($this->sbml_output_file)) {
            @unlink($this->sbml_output_file);
            $this->sbml_output_file = null;
        }
        if ($this->sif_output_file !== null && file_exists($this->sif_output_file)) {
            @unlink($this->sif_output_file);
            $this->sif_output_file = null;
        }
        $this->submit();
    }
}
