<?php

namespace App\Models;

use App\Exceptions\SecurityException;
use App\PHENSIM\Utils;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laratrust\Contracts\Ownable;

/**
 * App\Models\Job
 *
 * @property int                   $id
 * @property int                   $user_id
 * @property string                $job_key
 * @property string                $job_type
 * @property string                $job_status
 * @property array                 $job_parameters
 * @property array                 $job_data
 * @property string                $job_log
 * @property \Carbon\Carbon        $created_at
 * @property \Carbon\Carbon        $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|Job whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Job whereId($value)
 * @method static \Illuminate\Database\Query\Builder|Job whereJobData($value)
 * @method static \Illuminate\Database\Query\Builder|Job whereJobKey($value)
 * @method static \Illuminate\Database\Query\Builder|Job whereJobLog($value)
 * @method static \Illuminate\Database\Query\Builder|Job whereJobParameters($value)
 * @method static \Illuminate\Database\Query\Builder|Job whereJobStatus($value)
 * @method static \Illuminate\Database\Query\Builder|Job whereJobType($value)
 * @method static \Illuminate\Database\Query\Builder|Job whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Job whereUserId($value)
 * @mixin \Eloquent
 * @property-read string           $uri
 * @property string                $job_name
 * @method static Builder|Job whereJobName($value)
 */
class Job extends Model implements Ownable
{

    use PresentUri;

    public const QUEUED     = 'queued';
    public const PROCESSING = 'processing';
    public const COMPLETED  = 'completed';
    public const FAILED     = 'failed';

    protected static $route = 'api-get-job';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'job_parameters' => 'array',
        'job_data'       => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'job_key',
        'job_type',
        'job_status',
        'job_parameters',
        'job_data',
        'job_log',
        'job_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'user_id',
        'job_key',
        'job_data',
        'job_parameters',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'uri',
    ];

    /**
     * Checks if an user can list jobs
     *
     * @param \App\Models\User|null $user
     *
     * @return bool
     */
    public static function canListJobs(User $user = null): bool
    {
        if ($user === null) {
            $user = Auth::user();
        }
        if ($user === null) {
            return false;
        }

        return $user->hasRole('administrator') || $user->can('read-job');
    }

    /**
     * List Jobs
     *
     * @param string|null $statusFilter
     * @param string|null $jobType
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function listJobs(string $statusFilter = null, string $jobType = null): Builder
    {
        if (!self::canListJobs()) {
            abort(403, 'User is not allowed to view jobs');
        }
        $isAdmin = Auth::user() !== null && Auth::user()->isAdmin();
        $query = self::query();
        if (!$isAdmin) {
            $query->where('user_id', '=', Auth::user()->id);
        }
        if ($statusFilter !== null) {
            $query->where('job_status', '=', $statusFilter);
        }
        if ($jobType !== null) {
            $query->where('job_type', '=', $jobType);
        }

        return $query;
    }

    /**
     * Create a job
     *
     * @param string $type
     * @param array  $parameters
     * @param array  $jobData
     * @param string $jobName
     *
     * @return \App\Models\Job
     */
    public static function buildJob(string $type, array $parameters = [], array $jobData = [], $jobName = ''): Job
    {
        /** @var \App\Models\Job $job */
        $job = self::create(
            [
                'user_id'        => Auth::id(),
                'job_type'       => $type,
                'job_status'     => self::QUEUED,
                'job_parameters' => $parameters,
                'job_data'       => $jobData,
                'job_log'        => '',
                'job_name'       => $jobName,
            ]
        );

        return $job;
    }

    /**
     * User Model BelongsTo Relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Compute a job key
     *
     * @param string                   $jobType
     * @param integer|\App\Models\User $userId
     *
     * @return string
     */
    public static function computeKey($jobType, $userId): string
    {
        if ($userId instanceof User) {
            $userId = $userId->id;
        }

        return Utils::makeKey('type', $jobType, 'time', microtime(true), 'user_id', $userId);
    }

    /**
     * Get the name of the job
     *
     * @return string
     */
    public function getJobName(): string
    {
        $jn = trim($this->job_name);

        return (empty($jn)) ? "Job of " . $this->created_at->toDayDateTimeString() : $jn;
    }

    /**
     * Generate accession key for this job
     *
     * @return string
     */
    public function generateKey(): string
    {
        return self::computeKey($this->job_type, $this->user_id);
    }

    /**
     * Get the key for this job
     *
     * @return string
     */
    public function getJobKey(): string
    {
        if (!$this->job_key) {
            $this->job_key = $this->generateKey();
        }

        return $this->job_key;
    }

    /**
     * Get the value of a parameter for this job
     *
     * @param string $parameter
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParameter(string $parameter, $default = null)
    {
        return $this->job_parameters[$parameter] ?? $default;
    }

    /**
     * Get and cast a parameter
     *
     * @param string          $parameter
     * @param string|callable $type
     * @param null            $default
     * @param bool            $keepNull
     *
     * @return bool|float|int|mixed|null
     */
    public function getTypedParameter(string $parameter, $type, $default = null, $keepNull = true)
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
    public function setParameter(string $parameter, $value): self
    {
        $tmp = $this->job_parameters;
        $tmp[$parameter] = $value;
        $this->job_parameters = $tmp;

        return $this;
    }

    /**
     * Add parameters to this job
     *
     * @param array $parameters
     *
     * @return $this
     */
    public function addParameters(array $parameters): self
    {
        foreach ($parameters as $param => $value) {
            $this->setParameter($param, $value);
        }

        return $this;
    }

    /**
     * Set parameters to this job
     *
     * @param array $parameters
     *
     * @return $this
     */
    public function setParameters(array $parameters): self
    {
        $this->job_parameters = [];

        return $this->addParameters($parameters);
    }

    /**
     * Get the value of a parameter for this job
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getData($key, $default = null)
    {
        return $this->job_data[$key] ?? $default;
    }

    /**
     * Set the value of a parameter for this job
     *
     * @param array|string $key
     * @param mixed|null   $value
     *
     * @return $this
     */
    public function setData($key, $value = null): self
    {
        if (is_array($key)) {
            $this->job_data = [];

            return $this->addData($key);
        }

        $tmp = $this->job_data;
        $tmp[$key] = $value;
        $this->job_data = $tmp;

        return $this;
    }

    /**
     * Add parameters to this job
     *
     * @param array $key
     *
     * @return $this
     */
    public function addData($key): self
    {
        foreach ($key as $param => $value) {
            $this->setData($param, $value);
        }

        return $this;
    }

    /**
     * Set the log attribute.
     *
     * @param string $value
     */
    public function setJobLogAttribute($value): void
    {
        $aLines = explode("\n", $value);
        $value = implode(
            "\n",
            array_map(
                static function ($line) {
                    $line = preg_replace('/\033\[([0-9;]+)m/i', '', $line);
                    if (strpos($line, "\r") === false) {
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
        $this->attributes['log'] = $value;
    }

    /**
     * Append text to the log
     *
     * @param string  $text
     * @param boolean $appendNewLine
     * @param boolean $commit
     *
     * @return $this
     */
    public function appendLog($text, $appendNewLine = true, $commit = true): self
    {
        if ($appendNewLine) {
            $text .= "\n";
        }
        //echo $text; // @TODO FOR DEBUG ONLY
        $this->job_log = $this->job_log . $text;
        if ($commit) {
            $this->save();
        }

        return $this;
    }

    /**
     * Returns the path of the job storage directory
     *
     * @return string
     */
    public function getJobDirectory(): string
    {
        $path = Utils::getStorageDirectory('jobs') . DIRECTORY_SEPARATOR . $this->getJobKey();
        if (!file_exists($path)) {
            Utils::createDirectory($path);
        }

        return $path;
    }

    /**
     * Returns the path of a file in the job storage directory
     *
     * @param string $filename
     *
     * @return string
     */
    public function getJobFile($filename): string
    {
        return $this->getJobDirectory() . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Delete the job directory
     *
     * @return bool
     */
    public function deleteJobDirectory(): bool
    {
        return Utils::delete($this->getJobDirectory());
    }

    /**
     * Delete the model from the database.
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function delete()
    {
        if (!$this->canBeDeleted()) {
            throw new SecurityException('The current user is not allowed to update this job');
        }

        return parent::delete();
    }

    /**
     * Save the model to the database.
     *
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        if (!$this->canBeUpdated()) {
            throw new SecurityException('The current user is not allowed to update this job');
        }
        if (!$this->job_key) {
            $this->getJobKey();
        }

        return parent::save($options);
    }

    /**
     * Gets the owner key value inside the model or object
     *
     * @return mixed
     */
    public function ownerKey(): int
    {
        return $this->user_id;
    }

    /**
     * Checks if an user can create a job
     *
     * @param null|\App\Models\User $user
     *
     * @return bool
     */
    public static function canBeCreated(User $user = null): bool
    {
        if ($user === null) {
            $user = Auth::user();
        }
        if ($user === null) {
            return false;
        }

        return $user->hasRole('administrator') || $user->can('create-job');
    }

    /**
     * Checks if an user can update this job
     *
     * @param \App\Models\User|null $user
     *
     * @return bool
     */
    public function canBeRead(User $user = null): bool
    {
        if ($user === null) {
            $user = Auth::user();
        }
        if ($user === null) {
            return false;
        }

        return $user->hasRole('administrator') || $user->canAndOwns('read-job', $this);
    }

    /**
     * Checks if an user can update this job
     *
     * @param \App\Models\User|null $user
     *
     * @return bool
     */
    public function canBeUpdated(User $user = null): bool
    {
        if ($user === null) {
            $user = Auth::user();
        }
        if ($user === null) {
            return false;
        }

        return $user->hasRole('administrator') || $user->canAndOwns('update-job', $this);
    }

    /**
     * Checks if an user can delete this job
     *
     * @param \App\Models\User|null $user
     *
     * @return bool
     */
    public function canBeDeleted(User $user = null): bool
    {
        if ($user === null) {
            $user = Auth::user();
        }
        if ($user === null) {
            return false;
        }

        return $user->hasRole('administrator') || $user->canAndOwns('delete-job', $this);
    }

}
