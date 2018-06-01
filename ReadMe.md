**How to run the App**

- visit https://www.docker.com/ and install the Docker App and docker compose
- deploy an App and run docker-compose up
- open browser and access localhost:80

**App specs and details**

- PHP 5.6
- Based on https://getcomposer.org/ and http://php-di.org/


**Task list**

- Set up work environment to support PHP PSR-1/2 and PSR-4
- Pull code 
- Run the App and obtain access to the localhost:80
- Modify output to support multiple output formats (format should switch on inbound header request)
  - /index/hello?format=xml - should provide output in XML format
  - /index/hello?format=json - should provide output in JSON format
  - /index/hello?format=text - should provide output in text format
  - /index/hello? - should provide output in text format
  - format support should be applicable for all Controllers/Actions
- Modify code to support properties filtering via GET request
  - /booya/booya - should output all fields 
  - /booya/booya?fields=name,address - should output only name and address fields 
  - /booya/booya?fields=name - should output only name field
- Implement /booya/list
  - total number of entires generated 101
  - implement pagination support via GET request
    - /booya/list?page=2&page_size=20
    - default page_size is 20
    - page_size can't be more than 50
  - support fields filtering 
    - /booya/list - should output all fields 
    - /booya/list?fields=name,address - should output only name and address fields 
    - /booya/list?fields=name - should output only name field
  - support format switching
    - /booya/list?format=xml - should provide output in XML format
    - /booya/list?format=json - should provide output in JSON format
    - /booya/list?format=text - should provide output in text format
    - /booya/list? - should provide output in text format
  - make sure it's stable and works together
- Commit code and create a pull request
