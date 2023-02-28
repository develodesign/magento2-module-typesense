requirejs([
    'Develo_Typesense/js/typesense-adapter'
], function (TypesenseInstantSearchAdapter) {

    var isAvailable =
        typeof algoliaConfig !== 'undefined' &&
        typeof algoliaConfig.typesense !== 'undefined' ||
        typeof algolia !== 'undefined';
    if (!isAvailable) {
        return;
    }

    if (!algoliaConfig.typesense.isEnabled) {
        return;
    }

    var typesenseInstantsearchAdapter = new TypesenseInstantSearchAdapter({
        server: algoliaConfig.typesense.config,
        additionalSearchParameters: {
            query_by: 'name,categories'
        }
    });

    var searchClient = typesenseInstantsearchAdapter.searchClient;

    algolia.registerHook('beforeInstantsearchInit', function (instantsearchOptions) {

        searchClient.addAlgoliaAgent = function () {
            // do nothing, function is required.
        }

        instantsearchOptions.searchClient = searchClient;

        return instantsearchOptions;
    })
});
