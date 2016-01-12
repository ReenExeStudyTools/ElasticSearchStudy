#!/bin/sh
curl -XDELETE 'http://localhost:9200/rustest' && echo
curl -XPUT 'http://localhost:9200/rustest' -d '{
    "settings": {
		"analysis": {
			"analyzer": {
				"my_analyzer": {
					"type": "custom",
					"tokenizer": "standard",
					"filter": ["lowercase", "russian_morphology", "english_morphology"]
				}
			}
		}
	}
}' && echo
curl -XPUT 'http://localhost:9200/rustest/type1/_mapping' -d '{
	"type1": {
	    "_all" : {"analyzer" : "russian_morphology"},
    	"properties" : {
        	"body" : { "type" : "string", "analyzer" : "russian_morphology" }
    	}
	}
}' && echo
curl -XPUT 'http://localhost:9200/rustest/type1/1' -d '{"body": "женский "}' && echo
curl -XPUT 'http://localhost:9200/rustest/type1/2' -d '{"body": "женское"}' && echo
curl -XPUT 'http://localhost:9200/rustest/type1/3' -d '{"body": "женские"}' && echo
curl -XPUT 'http://localhost:9200/rustest/type1/4' -d '{"body": "женское"}' && echo
curl -XPUT 'http://localhost:9200/rustest/type1/5' -d '{"body": "женская"}' && echo
curl -XPUT 'http://localhost:9200/rustest/type1/6' -d '{"body": "мужское"}' && echo
curl -XPOST 'http://localhost:9200/rustest/_refresh' && echo
echo "Should return 5"
curl -s 'http://localhost:9200/rustest/type1/_search?pretty=true' -d '{"query": {"query_string": {"query": "body:женское"}}}'
