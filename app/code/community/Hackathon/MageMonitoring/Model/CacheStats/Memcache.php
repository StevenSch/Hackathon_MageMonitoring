<?php
class Hackathon_MageMonitoring_Model_CacheStats_Memcache implements Hackathon_MageMonitoring_Model_CacheStats {

    private $_memCachePool;
    private $_memCacheStats;

    public function __construct()
    {
        try {
            if ($this->_memCachePool == null && class_exists('Memcache', false)) {
                $localXml = simplexml_load_file('app/etc/local.xml', null, LIBXML_NOCDATA);

                if ($xr = $localXml->xpath('//cache/memcached/servers/server')) {
                    $this->_memCachePool = new Memcache;
                    foreach ($xr as $server) {
                        $host = (string)$server->host;
                        $port = (string)$server->port;
                        $this->_memCachePool->addServer($host, $port);
                    }
                    $this->_memCacheStats = $this->_memCachePool->getStats();
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function getId() {
        $o = array();
        preg_match("/.+_(.+)\z/", __CLASS__, $o);
        return strtolower($o[1]);
    }

    public function getName() {
        return 'Memcache';
    }

    public function getVersion() {
        return $this->_memCachePool->getVersion();
    }

    public function isActive() {
        if ($this->_memCachePool && $this->_memCachePool->getVersion()) {
            return true;
        }
        return false;
    }

    public function getMemoryMax() {
        if (isset($this->_memCacheStats['limit_maxbytes'])) {
            return $this->_memCacheStats['limit_maxbytes'];
        }
        return 0;
    }

    public function getMemoryUsed() {
        if (isset($this->_memCacheStats['bytes'])) {
            return $this->_memCacheStats['bytes'];
        }
        return 0;
    }

    public function getCacheHits() {
        if (isset($this->_memCacheStats['get_hits'])) {
            return $this->_memCacheStats['get_hits'];
        }
        return 0;
    }

    public function getCacheMisses() {
        if (isset($this->_memCacheStats['get_misses'])) {
            return $this->_memCacheStats['get_misses'];
        }
        return 0;
    }

    public function flushCache() {
        $this->_memCachePool->flush();
        return true;
    }

}