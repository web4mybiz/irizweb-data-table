# Irizweb Data Table Plugin
Basic plugin to create a custom table and perform CRUD operations with it. This plugin is built for a sample project. 
# Instructions
1. Upload plugin to your WordPress installation.
2. Activate plugin
3. Use the shortcode [iriz-dataform] to display the form.
4. Use the shortcode [iriz-datalist] to display the form.
# Custom API Endpoints
1. Data can be viewed /wp-json/irizweb-data-api/v1/data/
2. You can also push data to the same endpoint. Accepted parameters are 'name', 'email','address', 'city', 'state', 'country'
3. You need to use the API key to post data. You can find it in the code.

# Authentication
1. Open Postman
2. Click Authorization tab and select API Key as type.
3. Add 'DATA-API-KEY' as Key
4. Add the hard coded api key from the code as Value eg. 'd41d8cd98f00b204e9800998ecf8427e' you can change it in the code to any other keys or dynamic keys.

