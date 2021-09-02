<?php

use WP_Media_Folder\Aws\S3\S3Client;
use WP_Media_Folder\Aws\Sdk;

/**
 * Class WpmfAddonAWS3
 * WP_Media_Folder\Providers
 */
class WpmfAddonAWS3
{

    /**
     * Aws client
     *
     * @var Sdk
     */
    public $sdk_client;

    /**
     * S3 Client
     *
     * @var S3Client
     */
    public $aws3_client;

    /**
     * Regions list
     *
     * @var array
     */
    public $regions = array(
        'us-east-1'      => 'US East (N. Virginia)',
        'us-east-2'      => 'US East (Ohio)',
        'us-west-1'      => 'US West (N. California)',
        'us-west-2'      => 'US West (Oregon)',
        'ca-central-1'   => 'Canada (Central)',
        'ap-south-1'     => 'Asia Pacific (Mumbai)',
        'ap-northeast-2' => 'Asia Pacific (Seoul)',
        'ap-southeast-1' => 'Asia Pacific (Singapore)',
        'ap-southeast-2' => 'Asia Pacific (Sydney)',
        'ap-northeast-1' => 'Asia Pacific (Tokyo)',
        'eu-central-1'   => 'EU (Frankfurt)',
        'eu-west-1'      => 'EU (Ireland)',
        'eu-west-2'      => 'EU (London)',
        'eu-west-3'      => 'EU (Paris)',
        'sa-east-1'      => 'South America (Sao Paulo)',
    );

    /**
     * WpmfAddonAWS3 constructor.
     *
     * @param string $region Region
     */
    public function __construct($region = '')
    {
        // Autoloader.
        require_once WPMFAD_PLUGIN_DIR . '/class/Aws3/aws-autoloader.php';
        $args = get_option('_wpmfAddon_aws3_config');
        if (!empty($args)) {
            if (empty($args['signature_version'])) {
                $args['signature_version'] = 'v4';
            }

            if (empty($args['version'])) {
                $args['version'] = '2006-03-01';
            }

            if (!empty($region)) {
                $args['region'] = $region;
            }

            self::getClient($args);
            $this->aws3_client = self::getServiceClient($args);
        }
    }

    /**
     * Get client for the provider's SDK.
     *
     * @param array $args Params
     *
     * @return void
     */
    public function getClient($args)
    {
        $this->sdk_client = new Sdk($args);
    }

    /**
     * Get service client
     *
     * @param array $args Params
     *
     * @return S3Client
     */
    public function getServiceClient($args)
    {
        if (empty($args['region']) || $args['region'] === 'us-east-1') {
            $this->aws3_client = $this->sdk_client->createMultiRegionS3($args);
        } else {
            $this->aws3_client = $this->sdk_client->createS3($args);
        }

        return $this->aws3_client;
    }

    /**
     * Get all folders and files in a folder
     *
     * @param array $params Params list
     *
     * @return array
     */
    public function getFoldersFilesFromBucket($params)
    {
        $childs     = array();
        $nextMarker  = null;
        do {
            try {
                if ($nextMarker) {
                    $params['Marker'] = $nextMarker;
                }

                $objects = $this->aws3_client->listObjects($params);
                $childs    = array_merge($childs, $objects['Contents']);
                $nextMarker = $objects['NextMarker'];
            } catch (Exception $e) {
                print 'An error occurred: ' . esc_html($e->getMessage());
                $nextMarker = null;
            }
        } while ($nextMarker);

        return $childs;
    }

    /**
     * Create bucket.
     *
     * @param array $args Params
     *
     * @return void
     */
    public function createBucket($args)
    {
        $this->aws3_client->createBucket($args);
    }

    /**
     * Delete bucket.
     *
     * @param array $args Params
     *
     * @return \WP_Media_Folder\Aws\Result
     */
    public function deleteBucket($args)
    {
        return $this->aws3_client->deleteBucket($args);
    }

    /**
     * Check whether bucket exists.
     *
     * @param string $bucket Bucket name
     *
     * @return boolean
     */
    public function doesBucketExist($bucket)
    {
        return $this->aws3_client->doesBucketExist($bucket);
    }

    /**
     * Get region for bucket.
     *
     * @param array $args Params
     *
     * @return string
     */
    public function getBucketLocation($args)
    {
        $location = $this->aws3_client->getBucketLocation($args);
        $region   = empty($location['LocationConstraint']) ? '' : $location['LocationConstraint'];

        return $region;
    }

    /**
     * Get bucket details
     *
     * @param array $args Params
     *
     * @return \WP_Media_Folder\Aws\Result
     */
    public function getPublicAccessBlock($args = array())
    {
        $result = $this->aws3_client->getPublicAccessBlock($args);
        return $result;
    }

    /**
     * List buckets.
     *
     * @param array $args Params
     *
     * @return array
     */
    public function listBuckets($args = array())
    {
        return $this->aws3_client->listBuckets($args)->toArray();
    }

    /**
     * Check object exists in bucket.
     *
     * @param string $bucket  Bucket name
     * @param string $key     Object Key
     * @param array  $options Óptions
     *
     * @return boolean
     */
    public function doesObjectExist($bucket, $key, $options = array())
    {
        return $this->aws3_client->doesObjectExist($bucket, $key, $options);
    }

    /**
     * List objects.
     *
     * @param array $args Params list
     *
     * @return array
     */
    public function listObjects($args = array())
    {
        return $this->aws3_client->listObjects($args)->toArray();
    }

    /**
     * Upload file to bucket.
     *
     * @param array $args Params list
     *
     * @return void
     */
    public function uploadObject($args)
    {
        $this->aws3_client->putObject($args);
    }

    /**
     * Delete object from bucket.
     *
     * @param array $args Params list
     *
     * @return void
     */
    public function deleteObject($args)
    {
        $this->aws3_client->deleteObject($args);
    }

    /**
     * Copies object
     *
     * @param array $item Params
     *
     * @return \WP_Media_Folder\Aws\Result
     */
    public function copyObject($item)
    {
        return $this->aws3_client->copyObject($item);
    }

    /**
     * Get object
     *
     * @param array $args Params list
     *
     * @return void
     */
    public function getObject($args)
    {
        $this->aws3_client->getObject($args);
    }
}
