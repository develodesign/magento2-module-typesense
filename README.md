# Magento 2 Typesense Search Integration Module

This module integrates the Typesense search engine with Magento, providing faster and more accurate search results for your customers.

# Demo
You can see a demo of both Typesense used for the Autocomplete search bar and Typesense powered Category page rendering with filters. 
https://typesense-demo.develodesign.co.uk/

## Installation

### Composer Installation

You can install the module via Composer. Run the following command in your Magento 2 root directory:

```
composer require develodesign/magento2-module-typesense
```

### Copying the Module

Alternatively, you can copy the module files to the `app/code/Develo/Typesense` directory in your Magento 2 installation.

```
php bin/magento module:enable Develo_Typesense
php bin/magento module:enable Algolia_AlgoliaSearch
php bin/magento setup:upgrade
php bin/magento setup:di:compile
bin/magento setup:static-content:deploy
```

That's it! The develodesign/magento2-module-typesense module is now installed on your Magento 2 store.

## Configuration

System > Configuration > General > Typesense Search:

- "Enabled": A yes/no field to enable or disable the Typesense adapter.
- "Cloud ID": A text field to enter the Typesense cloud ID.
- "Admin API Key": A secret key to enter the Typesense admin API key.
- "Search Only Key": A public key to enter the Typesense search only key.
- "Nodes": A text field to enter the Typesense nodes.
- "Port": A text field to enter the Typesense port number.
- "Path": A text field to enter the Typesense path.
- "Protocol": A dropdown field to select the communication protocol.
- "Index Method": A dropdown field to select where the data should be indexed.

These options allow users to configure the Typesense adapter module and customize its behavior according to their needs.

***After enabling the Typesense module, if a user makes any changes to the configuration, the module will need to drop and rebuild the collections. As a result, the user will need to perform a full Magento reindex after making any configuration changes. This is important to keep in mind to ensure that the search results are accurate and up-to-date.***

Note that users also need to configure the Algolia module to fit you requirements. However, live credentials are not needed as our module acts as an adapter.

The Typesense module uses the Algolia settings, so users should configure Algolia as they normally would. It's important to note that if you set a facet, you must also set it in the product attribute section.

For more information on customizing the Algolia module, please refer to the following links:

- [Customizing Autocomplete Menu](https://www.algolia.com/doc/integration/magento-2/customize/autocomplete-menu/)
- [Customizing Instant Search Page](https://www.algolia.com/doc/integration/magento-2/customize/instant-search-page/)
- [Customizing Custom Front-end Events](https://www.algolia.com/doc/integration/magento-2/customize/custom-front-end-events/)

When migrating from Algolia, you will need to remove "Price" from the facets and review the Product and Category searchable attributes. Typesense is much more strict when querying so if an attribute does not exist it will throw an error.

Review the following config and set searchable to "No" when applicable:

Settings > Algolia > Products > Attributes

## Debugging config

You may get errors such as:

`pesense-adapter.js:1 Uncaught (in promise) Error: 404 - Could not find a field named "path" in the schema.`

This is because you either have a searchable attribute for products which does not exist, or perhaps a facet attribute which does not exist. You should remove the attribute from these areas and try again.

## Documentation

For more information about Typesense, check out their [official documentation](https://typesense.org/docs/).

You can also check out [Algolia's Magento 2 module](https://github.com/algolia/algoliasearch-magento-2).

## Contributors

| Name           | Email                                    | Twitter                       |
| -------------- | ---------------------------------------- | ----------------------------- |
| Luke Collymore | [luke@develodesign.co.uk](mailto:luke@develodesign.co.uk) | [@lukecollymore](https://twitter.com/lukecollymore) |
| Nathan McBride | [nathan@brideo.co.uk](mailto:nathan@brideo.co.uk)    | [@brideoweb](https://twitter.com/brideoweb)    |


### How to Contribute

Contributions are always welcome. If you have any suggestions or find any issues, please create a GitHub issue or fork the repository and submit a pull request.

Here's how to contribute:

1. Fork the project.
2. Create your feature branch (`git checkout -b feature/AmazingFeature`).
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`).
4. Push to the branch (`git push origin feature/AmazingFeature`).
5. Open a pull request.

## Acknowledgments

Algolia for creating a great product indexing and search configuration module

* [Algolia Open Source Module](https://github.com/algolia/algoliasearch-magento-2)


## Built and Maintained by 

<a href="http://www.develodesign.co.uk" rel="Develo">![Develo](http://www.develodesign.co.uk/develo-logo.png)</a>

