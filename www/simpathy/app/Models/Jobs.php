<?php

namespace App\Models;

use App\SIMPATHY\Utils;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Jobs
 *
 * @property int            $id
 * @property int            $user_id
 * @property string         $job_key
 * @property string         $job_type
 * @property string         $job_status
 * @property array          $job_parameters
 * @property array          $job_data
 * @property string         $job_log
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Jobs whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Jobs whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Jobs whereJobData($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Jobs whereJobKey($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Jobs whereJobLog($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Jobs whereJobParameters($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Jobs whereJobStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Jobs whereJobType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Jobs whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Jobs whereUserId($value)
 * @mixin \Eloquent
 */
class Jobs extends Model
{
    const QUEUED     = 'queued';
    const PROCESSING = 'processing';
    const COMPLETED  = 'completed';
    const FAILED     = 'failed';

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
        'user_id', 'job_key', 'job_type', 'job_status', 'job_parameters', 'job_data', 'job_log',
    ];

    /**
     * Compute a job key
     *
     * @param string  $jobType
     * @param array   $jobParameters
     * @param integer $userId
     *
     * @return string
     */
    public static function computeKey($jobType, array $jobParameters, $userId)
    {
        return Utils::makeKey('type', $jobType, 'parameters', $jobParameters, 'user_id', $userId);
    }

    /**
     * Generate accession key for this job
     *
     * @return string
     */
    public function generateKey()
    {
        return self::computeKey($this->job_type, $this->job_parameters, $this->user_id);
    }

    /**
     * Get the key for this job
     *
     * @return string
     */
    public function getJobKey()
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
    public function getParameter($parameter, $default = null)
    {
        return (isset($this->job_parameters[$parameter])) ? $this->job_parameters[$parameter] : $default;
    }

    /**
     * Set the value of a parameter for this job
     *
     * @param string $parameter
     * @param mixed  $value
     *
     * @return $this
     */
    public function setParameter($parameter, $value)
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
    public function addParameters($parameters)
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
    public function setParameters($parameters)
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
        return (isset($this->job_data[$key])) ? $this->job_data[$key] : $default;
    }

    /**
     * Set the value of a parameter for this job
     *
     * @param array|string $key
     * @param mixed|null   $value
     *
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            $this->job_data = [];
            return $this->addData($key);
        } else {
            $tmp = $this->job_data;
            $tmp[$key] = $value;
            $this->job_data = $tmp;
        }
        return $this;
    }

    /**
     * Add parameters to this job
     *
     * @param array $key
     *
     * @return $this
     */
    public function addData($key)
    {
        foreach ($key as $param => $value) {
            $this->setData($param, $value);
        }
        return $this;
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
    public function appendLog($text, $appendNewLine = true, $commit = true)
    {
        if ($appendNewLine) {
            $text .= "\n";
        }
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
    public function getJobDirectory()
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
    public function getJobFile($filename)
    {
        return $this->getJobDirectory() . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Delete the job directory
     *
     * @return bool
     */
    public function deleteJobDirectory()
    {
        return Utils::delete($this->getJobDirectory());
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
        if (!$this->job_key) {
            $this->getJobKey();
        }
        return parent::save($options);
    }
}
