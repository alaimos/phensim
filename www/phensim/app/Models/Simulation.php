<?php
/**
 * PHENSIM: Phenotype Simulator
 * @version 2.0.0.2
 * @author  Salvatore Alaimo, Ph.D.
 */

namespace App\Models;

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
    protected array $fillable = [
        'name',
        'status',
        'parameters',
        'data',
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
    protected array $casts = [
        'parameters' => 'array',
        'data'       => 'array',
        'public'     => 'boolean',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected array $attributes = [
        'parameters' => [],
        'data'       => [],
        'public'     => false,
        'public_key' => null,
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected array $appends = [
        'readable_status',
    ];

    /**
     * Scope a query to only include visible simulations to an user.
     * If the user is an admin all simulations are visible.
     * If the user is not logged in no simulations are visible.
     * In all other cases shows only the owned simulations
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User|null                 $user
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
     * @param string $value
     */
    public function setJobLogAttribute(string $value): void
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
        $this->attributes['job_log'] = $value;
    }

    /**
     * Checks for a valid state and sets the value in this model.
     * If an invalid state is provided, it will be replaced with a FAILED state.
     *
     * @param int $value
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
     * @param string  $text
     * @param boolean $appendNewLine
     * @param boolean $commit
     *
     * @return $this
     */
    public function appendLog(string $text, bool $appendNewLine = true, bool $commit = true): self
    {
        if ($appendNewLine) {
            $text .= "\n";
        }
        $this->job_log .= $text;
        if ($commit) {
            $this->save();
        }

        return $this;
    }

    /**
     * Get the value of a parameter for this job
     *
     * @param string $parameter
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParameter(string $parameter, $default = null): mixed
    {
        return $this->parameters[$parameter] ?? $default;
    }

    /**
     * Get and cast a parameter
     *
     * @param string          $parameter
     * @param string|callable $type
     * @param null|mixed      $default
     * @param bool            $keepNull
     *
     * @return mixed
     */
    public function getTypedParameter(string $parameter, callable|string $type, mixed $default = null, bool $keepNull = true): mixed
    {
        $value = $this->getParameter($parameter, $default);
        if ($keepNull && empty($value)) {
            return null;
        }
        if ($type === 'int') {
            return (int)$value;
        }
        if ($type === 'bool' || $type === 'boolean') {
            return (bool)$value;
        }
        if ($type === 'numeric' || $type === 'double' || $type === 'float') {
            return (float)$value;
        }
        if ($type === 'array') {
            return (array)$value;
        }
        if (is_callable($type)) {
            return $type($value);
        }

        return $value;
    }

    /**
     * Set the value of a parameter for this job
     *
     * @param string $parameter
     * @param mixed  $value
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

    /**
     * Get the value of a parameter for this job
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getData(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Set the value of a parameter for this job
     *
     * @param string     $key
     * @param mixed|null $value
     *
     * @return $this
     */
    public function setData(string $key, mixed $value = null): self
    {
        $tmp = $this->data;
        $tmp[$key] = $value;
        $this->data = $tmp;

        return $this;
    }

    //endregion

    //region Job Directory Stuff

    /**
     * Returns the absolute path of the folder where all files of this simulation will be stored
     * If the directory does not exist it will be created.
     *
     * @return string
     * @throws \App\Exceptions\FileSystemException
     */
    public function jobDirectory(): string
    {
        return Utils::getStorageDirectory('jobs/' . $this->id);
    }

    /**
     * Returns the absolute path of a file in the job storage directory
     *
     * @param string $filename
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
}
