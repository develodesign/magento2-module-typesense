const initAutoComplete = () => {
    algoliaBundle.$(function ($) {

        const {autocomplete} = window['@algolia/autocomplete-js'];
        const {resultURL} = algoliaConfig;

        if (algoliaConfig.autocomplete.nbOfProductsSuggestions > 0) {
            algoliaConfig.autocomplete.sections.unshift({
                hitsPerPage: algoliaConfig.autocomplete.nbOfProductsSuggestions,
                label: algoliaConfig.translations.products,
                name: "products"
            });
        }

        if (algoliaConfig.autocomplete.nbOfCategoriesSuggestions > 0) {
            algoliaConfig.autocomplete.sections.unshift({
                hitsPerPage: algoliaConfig.autocomplete.nbOfCategoriesSuggestions,
                label: algoliaConfig.translations.categories,
                name: "categories"
            });
        }

        if (algoliaConfig.autocomplete.nbOfQueriesSuggestions > 0) {
            algoliaConfig.autocomplete.sections.unshift({
                hitsPerPage: algoliaConfig.autocomplete.nbOfQueriesSuggestions,
                label: '',
                name: "suggestions"
            });
        }

        algoliaConfig.autocomplete.templates = {
            products: algoliaBundle.Hogan.compile($('#autocomplete_products_template').html()),
            categories: algoliaBundle.Hogan.compile($('#autocomplete_categories_template').html()),
            pages: algoliaBundle.Hogan.compile($('#autocomplete_pages_template').html())
        };

        const getQueryBy = function (name) {
            if (
                typeof algoliaConfig.typesense_searchable !== 'undefined' &&
                typeof algoliaConfig.typesense_searchable[name] !== 'undefined'
            ) {
                return algoliaConfig.typesense_searchable[name];
            }

            return 'name'
        }

// taken from common.js (autocomplete v0) and adopted to autocomplete v1
        const getAutocompleteSource = function ({section, setContext}) {
            if (section.hitsPerPage <= 0)
                return null;

            var options = {
                hitsPerPage: section.hitsPerPage,
                analyticsTags: 'autocomplete',
                clickAnalytics: true
            };

            var source = {};

            var templates = {};

            switch (section.name) {
                case 'products':
                    options.numericFilters = 'visibility_search=1';
                    options.ruleContexts = ['magento_filters', '']; // Empty context to keep BC for already create rules in dashboard
                    break;
                case 'categories':
                    if (algoliaConfig.showCatsNotIncludedInNavigation === false) {
                        options.numericFilters = 'include_in_menu=1';
                    }
                    break;
            }

            templates = {
                header({html}) {
                    return html`<h3>${section.label}</h3>`;
                },
                item({item, html}) {
                    const innerHtml = algoliaConfig.autocomplete.templates[section.name].render(item);

                    return html`<div dangerouslySetInnerHTML=${{ __html: innerHtml }}></div>`
                }
            }

            source = {
                ...options,
                indexName: algoliaConfig.indexName + "_" + section.name,
                name: section.name,
                templates
            };

            return source;
        };

        const plugins = []

        if (window.initExtraAlgoliaConfiguration) {
            const {plugins: extraPlugins} = initExtraAlgoliaConfiguration(algoliaConfig)
            plugins.push(...extraPlugins)
        }


        autocomplete({
                container: '#algolia-autocomplete-container',
                placeholder: algoliaConfig.placeholder,
                debug: algoliaConfig.autocomplete.isDebugEnabled,
                plugins,
                detachedMediaQuery: 'none',
                onSubmit: (params) => {
                    window.location.href = `${resultURL}?q=${params.state.query}`
                },
                classNames: {
                    list: 'w-full flex flex-wrap py-4 px-2',
                    item: 'w-full lg:w-1/2 p-2 hover:bg-gray-200',
                    sourceHeader: 'px-2 py-4 uppercase tracking-widest text-blue-500',
                    source: 'flex flex-col',
                    panel: 'mx-4 absolute w-full bg-white z-50 border border-gray-300',
                    input: 'w-full p-2 text-base lg:text-lg leading-7 tracking-wider border border-gray-300',
                    form: 'w-full relative flex items-center',
                    inputWrapper: 'flex-grow px-4',
                    inputWrapperPrefix: 'hidden',
                    inputWrapperSuffix: 'hidden',
                    label: 'm-0 leading-none',
                    submitButton: 'leading-none'
                },
                async getSources({query, setContext}) {
                    /** Setup autocomplete data sources **/
                    var sources = [];
                    for (let i = 0; i < algoliaConfig.autocomplete.sections.length; i++) {

                        let section = algoliaConfig.autocomplete.sections[i];

                        var source = getAutocompleteSource({section, setContext});

                        // autocomplete v1 adapter
                        if (source) {

                            let typesenseClient = new Typesense.Client(algoliaConfig.typesense.config)

                            const results = await typesenseClient.collections(source.indexName).documents().search({
                                q: query,
                                query_by: getQueryBy(source.name),
                                per_page: source.hitsPerPage
                            })

                            sources.push({
                                sourceId: source.name,
                                query,
                                getItems() {
                                    return results.hits.map(hit => (
                                        hit.document
                                    ));
                                },
                                templates: source.templates
                            });
                        }
                    }

                    return sources;
                },

                render({elements, render, html}, root) {
                    const {categories, pages, products} = elements;

                    render(
                        html`<div class="relative w-full flex flex-col lg:flex-row">
      <div class="w-full lg:order-1 lg:border-l-2">${products}</div>
      <div class="w-full px-1 pb-10 md:px-2">${categories} ${pages}</div>
      </div>`,
                        root
                    );
                },

                renderNoResults({state, render, html}, root) {
                    const suggestions = [];

                    if (algoliaConfig.showSuggestionsOnNoResultsPage && algoliaConfig.popularQueries.length > 0) {
                        algoliaConfig.popularQueries
                            .slice(0, Math.min(3, algoliaConfig.popularQueries.length))
                            .forEach(function (query) {
                                suggestions.push({
                                    url: algoliaConfig.baseUrl + '/catalogsearch/result/?q=' + encodeURIComponent(query),
                                    query
                                });
                            });
                    }

                    render(html`
      <div class="p-4 lg:p-6">
        <div class="lx:mb-2">
          <span class="pr-1 text-base xl:text-lg font-bold tracking-wide">${algoliaConfig.translations.noProducts}</span>
          <span class="text-base font-bold tracking-wide">"${state.query}"</span>
        </div>
        <div class="see-all">
            ${(algoliaConfig.showSuggestionsOnNoResultsPage && suggestions.length > 0 ?
                        html`<div class="py-4 lg:py-6">
                <span class="pr-1 text-sm xl:text-base font-bold tracking-wider">${algoliaConfig.translations.popularQueries}</span>
                ${suggestions.map(({url, query}) => html`<a class="text-sm xl:text-base text-gray-600 tracking-wide font-semibold hover:underline" href="${url}">${query}</a>`)}
                </div>` : '')
                    }
            <a class="py-2 text-sm xl:text-base text-gray-600 tracking-wide font-bold hover:underline" href="${algoliaConfig.baseUrl}/catalogsearch/result/?q=__empty__">${algoliaConfig.translations.seeAll}</a>
        </div>
      </div>`, root);
                },
            }
        );
    })
};
