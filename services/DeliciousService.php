<?php

include_once 'model/Feed.php';

class DeliciousService
{
    /**
     * 
     * @var DbService
     */
    public $dbService;

    /**
     * Get a feed
     *
     * @param int $id
     * @return Feed
     */
    public function getFeed($url)
    {
        $feed = $this->dbService->getByField(array('url'=>$url), 'Feed');
        
        $update = $feed == null || strtotime($feed->updated) < (time() - 60 * 15); 
        
        if ($feed == null) {
            $feed = new Feed();
        }
        
        if ($update) {
            $this->log->debug("Loading feed $url");
            // make the request!
            // $feed->content = 
            $content = null;
            try {
                $client = Zend_Feed::getHttpClient();
	            $client->setUri($url);
	            $response = $client->request('GET');
	            if ($response->getStatus() !== 200) {
	                throw new Zend_Feed_Exception('Feed failed to load, got response code ' . $response->getStatus());
	            }
	            $content = $response->getBody();
	            
            } catch (Exception $zfe) {
                $this->log->err("Failed loading feed from $url");
                return $content;
            }

            $feed->content = $content;
            $feed->url = $url;
            $this->dbService->saveObject($feed);
        }

        return $feed;
    }
}

?>