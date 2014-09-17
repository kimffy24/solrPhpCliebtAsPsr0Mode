SolrPhpClient rebuild layout as psr-0 mode

   Project copy from http://code.google.com/p/solr-php-client/downloads/list 
   Source file from SolrPhpClient.r60.2011-05-04.zip 
   At 2014年09月17日 星期三 13时10分06秒 
   
     importance: 非本人原创作品。
     importance: 纯属一时兴起。
     importance: 原项目地址: http://code.google.com/p/solr-php-client
   
    1. using namespace to replace 'require_once()'
    2. locate at Apache\Solr
    3. rename as psr-0 mode
    4. rename Apache_Solr_HttpTransport_Abstract as Apache\Solr\HttpTransport\HttpTransportAbstract
       rename Apache_Solr_HttpTransport_Interface as Apache\Solr\HttpTransport\HttpTransportInterface
       cause Interface and Abstract it a keyword in php
       
       layout:
       apache/
		├── ChangeLog
		├── COPYING
		├── library
		│   └── Apache
		│       └── Solr
		│           ├── Document.php
		│           ├── Exception.php
		│           ├── HttpTransport
		│           │   ├── CurlNoReuse.php
		│           │   ├── Curl.php
		│           │   ├── FileGetContents.php
		│           │   ├── HttpTransportAbstract.php
		│           │   ├── HttpTransportInterface.php
		│           │   └── Response.php
		│           ├── HttpTransportException.php
		│           ├── InvalidArgumentException.php
		│           ├── NoServiceAvailableException.php
		│           ├── ParserException.php
		│           ├── Response.php
		│           ├── Service
		│           │   └── Balancer.php
		│           └── Service.php
		└── README.md


    How to use in composer autoloader?
        1. copy apache to vendor
        2. add 
                'Apache' => array($vendorDir . '/solrPhpCliebtAsPsr0Mode/library'),
            to vendor/composer/autoload_namespaces.php
            
    How to use in project
        add 
            use Apache\Solr\Service;
        at the entrance of the project.
        
/*
 * 注意
 * 仅作研究
 */
