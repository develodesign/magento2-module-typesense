const { autocomplete, getAlgoliaResults, getAlgoliaFacets } = window['@algolia/autocomplete-js'];
const { applicationId, apiKey, indexName, resultURL } = algoliaConfig;
const { createQuerySuggestionsPlugin } = window['@algolia/autocomplete-plugin-query-suggestions'];

const searchClient = algoliasearch(applicationId, apiKey);

if (algoliaConfig.autocomplete.nbOfProductsSuggestions > 0) {
    algoliaConfig.autocomplete.sections.unshift({ hitsPerPage: algoliaConfig.autocomplete.nbOfProductsSuggestions, label: algoliaConfig.translations.products, name: "products"});
}

if (algoliaConfig.autocomplete.nbOfCategoriesSuggestions > 0) {
    algoliaConfig.autocomplete.sections.unshift({ hitsPerPage: algoliaConfig.autocomplete.nbOfCategoriesSuggestions, label: algoliaConfig.translations.categories, name: "categories"});
}

if (algoliaConfig.autocomplete.nbOfQueriesSuggestions > 0) {
    algoliaConfig.autocomplete.sections.unshift({ hitsPerPage: algoliaConfig.autocomplete.nbOfQueriesSuggestions, label: '', name: "suggestions"});
}

// taken from common.js (autocomplete v0) and adopted to autocomplete v1
const getAutocompleteSource = function ({ section, setContext }) {
    if (section.hitsPerPage <= 0)
        return null;

    var options = {
        hitsPerPage: section.hitsPerPage,
        analyticsTags: 'autocomplete',
        clickAnalytics: true
    };

    var source;

    if (section.name === "products") {
        options.numericFilters = 'visibility_search=1';
        options.ruleContexts = ['magento_filters', '']; // Empty context to keep BC for already create rules in dashboard

        source =  Object.assign({
            source: searchClient.initIndex(algoliaConfig.indexName + "_" + section.name),
        }, options, {
            name: section.name,
            transformResponse({ results, hits }) {
                setContext({
                    nbProducts: results[0].nbHits,
                });

                return hits;
            },
            templates: {
                footer({ state, html }) {
                    const categoryLinks = []
                    const endcodedQuery = encodeURIComponent(state.query)

                    const firstCategories = state.context.productsFacetHits
                        .sort((a, b) => b.count - a.count)
                        .slice(0, 2)

                    if (firstCategories) {
                        for (var i = 0; i<algoliaConfig.facets.length; i++) {
                            if (algoliaConfig.facets[i].attribute == "categories") {
                                categoryLinks.push(
                                    ...
                                        firstCategories.map(facetHit => {
                                            const key = facetHit.label
                                            return {
                                                key,
                                                url: algoliaConfig.baseUrl + '/catalogsearch/result/?q=' + endcodedQuery  + '#q=' + endcodedQuery + '&hFR[categories.level0][0]=' + encodeURIComponent(key) + '&idx=' + algoliaConfig.indexName + '_products'
                                            }
                                        })
                                )
                            }
                        }
                    }

                    var allUrl = algoliaConfig.baseUrl + '/catalogsearch/result/?q=' + endcodedQuery;

                    return html`<div id="autocomplete-products-footer">
          <div class="hidden lg:block py-2.5 bg-gray-100 text-center text-base leading-5">
          <span>
            ${algoliaConfig.translations.seeIn}
            <a class="pl-1 text-blue-500 font-semibold hover:underline" href="${allUrl}">${algoliaConfig.translations.allDepartments}</a>
          </span> (${state.context.nbProducts})
          ${algoliaConfig.instant.enabled && categoryLinks.length > 0
                        ? html` ${algoliaConfig.translations.orIn} ${categoryLinks.map(({ key, url }) => html`<a class="text-blue-500 uppercase font-semibold hover:underline" href="${url}">${key}</a> `)}` : ''}
            </div>
            ${algoliaConfig.removeBranding === false ?
                        html`
                <div class="footer_algolia absolute w-full lg:w-1/3 left-0 bottom-0 p-2.5">
                  <a href="https://www.algolia.com/?utm_source=magento&utm_medium=link&utm_campaign=magento_autocompletion_menu" title="Search by Algolia" target="_blank">
                    <img class="mx-auto" src="${algoliaConfig.urls.logo}"  alt="Search by Algolia" />
                  </a>
                </div>`
                        : ''
                    }
          </div>`
                },
                header({ html }) {
                    return html`<span class="lg:hidden px-2 py-4">${algoliaConfig.translations.products}</span>`;
                },
                item({ item, components, html }) {
                    return html`
          <a href="${item.url}"class="flex h-full p-2">
            <div class="w-1/4">
              <img
                src="${item.image_url}"
                alt="${item.name}"
                width="100"
                height="100"
              />
            </div>
            <div class="w-3/4 pl-1 md:pl-2 flex-grow">
              <div class="highlight text-base tracking-wider">
                ${components.Highlight({
                        hit: item,
                        attribute: 'name',
                        tagName: 'em'
                    })}
              </div>
              <div class="text-sm">${item.categories_without_path[0]}</div>
              <div class="flex items-end py-1 md:py-2">
                <span class="pr-2 text-base font-medium tracking-wider leading-none text-blue-500">${item.price[algoliaConfig.currencyCode].default_formated}</span>
                <span class="text-sm tracking-wider line-through leading-none text-gray-500">${item.price[algoliaConfig.currencyCode].default_original_formated ? item.price[algoliaConfig.currencyCode].default_original_formated:""}</span>
              </div>
            </div>
          </a>`
                }
            }
        });
    }
    else if (section.name === "categories" || section.name === "pages")
    {
        if (section.name === "categories" && algoliaConfig.showCatsNotIncludedInNavigation === false) {
            options.numericFilters = 'include_in_menu=1';
        }

        let templates

        if (section.name === 'categories') {
            templates = {
                header({ items, html }) {
                    if (items.length === 0) {
                        return null;
                    }
                    return html`<span>${algoliaConfig.translations.categories}</span>`;
                },
                item({ item, components, html }) {
                    return html`
          <a href="${item.url}" class="w-full h-1/2">
              <span class="highlight">
                ${components.Highlight({
                        hit: item,
                        attribute: 'path',
                        tagName: 'em'
                    })}
              </span>
          </a>`
                }
            }
        } else {
            templates = {
                item({ item, components, html }) {
                    return html`<a href="${item.url}" class="h-full flex flex-col">
                        <span class="highlight text-base tracking-wider pr-2">
                          ${components.Highlight({
                        hit: item,
                        attribute: 'name',
                        tagName: 'em'
                    })}
                        </span>
                        <span class="highlight text-sm highlight w-full truncate text-gray-500">
                          ${components.Highlight({
                        hit: item,
                        attribute: 'content',
                        tagName: 'em'
                    })}
                        </span>
                    </a>`
                }
            }
        }

        source =  {
            ...options,
            source: searchClient.initIndex(algoliaConfig.indexName + "_" + section.name),
            name: section.name,
            templates
        };
    }
    else if (section.name === "suggestions")
    {
        /// popular queries/suggestions
        // todo adopt to autocomplete v1
        var suggestions_index = searchClient.initIndex(algoliaConfig.indexName + "_suggestions");
        var products_index = searchClient.initIndex(algoliaConfig.indexName + "_products");

        source = {
            source: 'query',
            source: products_index,
            facets: ['categories.level0'],
            hitsPerPage: 0,
            typoTolerance: false,
            maxValuesPerFacet: 1,
            analytics: false,
            displayKey: 'query',
            name: section.name,
            templates: {
                suggestion: function (hit, payload) {
                    if (hit.facet) {
                        hit.category = hit.facet.value;
                    }

                    if (hit.facet && hit.facet.value !== algoliaConfig.translations.allDepartments) {
                        hit.url = algoliaConfig.baseUrl + '/catalogsearch/result/?q=' + hit.query + '#q=' + hit.query + '&hFR[categories.level0][0]=' + encodeURIComponent(hit.category) + '&idx=' + algoliaConfig.indexName + '_products';
                    } else {
                        hit.url = algoliaConfig.baseUrl + '/catalogsearch/result/?q=' + hit.query;
                    }

                    var toEscape = hit._highlightResult.query.value;
                    hit._highlightResult.query.value = algoliaBundle.autocomplete.escapeHighlightedString(toEscape);

                    hit.__indexName = algoliaConfig.indexName + "_" + section.name;
                    hit.__queryID = payload.queryID;
                    hit.__position = payload.hits.indexOf(hit) + 1;

                    return algoliaConfig.autocomplete.templates.suggestions.render(hit);
                }
            }
        };
    } else {
        /** If is not products, categories, pages or suggestions, it's additional section **/
        var index = searchClient.initIndex(algoliaConfig.indexName + "_section_" + section.name);

        source = {
            source: index,
            displayKey: 'value',
            ...options,
            name: section.name,
            templates: {
                item: function (hit, payload) {
                    console.warn(`Missing renderer for ${section.name}, add one inside autocompleteConfig.js`)
                }
            }
        };
    }

    if (section.name !== 'suggestions' && section.name !== 'products') {
        source.templates.header = ({ html }) => html`<div class="category">${section.label ? section.label : section.name}</div>`;
    }

    return source;
};

const plugins = []

if (window.initExtraAlgoliaConfiguration) {
    const { plugins: extraPlugins } = initExtraAlgoliaConfiguration(algoliaConfig)
    plugins.push(...extraPlugins)
}


autocomplete({
        container:'#algolia-autocomplete-container',
        placeholder: algoliaConfig.placeholder,
        debug: algoliaConfig.autocomplete.isDebugEnabled,
        plugins,
        detachedMediaQuery: 'none',
        onSubmit: (params)=>{
            window.location.href =`${resultURL}?q=${params.state.query}`
        },
        classNames:{
            list:'w-full flex flex-wrap',
            item:'w-full lg:w-1/2 flex-grow p-2 hover:bg-gray-200',
            sourceHeader:'px-2 py-4 uppercase tracking-widest text-blue-500',
            source:'flex flex-col',
            panel:'mx-4 absolute w-full bg-white z-50 border border-gray-300',
            input:'w-full p-2 text-base lg:text-lg leading-7 tracking-wider border border-gray-300',
            form:'w-full relative flex items-center',
            inputWrapper:'flex-grow px-4',
            inputWrapperPrefix:'hidden',
            inputWrapperSuffix:'hidden',
            label:'m-0 leading-none',
            submitButton:'leading-none'
        },
        getSources({ query, setContext }) {
            /** Setup autocomplete data sources **/
            var sources = [];
            algoliaConfig.autocomplete.sections.forEach(function (section) {
                var source = getAutocompleteSource({ section, setContext });

                // autocomplete v1 adapter
                if (source) {
                    sources.push({
                        sourceId: source.name,
                        query,
                        getItems() {
                            const params = {
                                hitsPerPage: source.hitsPerPage
                            }
                            if (source.numericFilters) {
                                params.numericFilters = source.numericFilters
                            }
                            if (source.ruleContexts) {
                                params.ruleContexts = source.ruleContexts
                            }
                            if (source.clickAnalytics) {
                                params.clickAnalytics = source.clickAnalytics
                            }
                            const resultsConfig = {
                                searchClient,
                                queries: [
                                    {
                                        indexName: source.source.indexName,
                                        query,
                                        params
                                    },
                                ],
                            }

                            if (source.transformResponse) {
                                resultsConfig.transformResponse = source.transformResponse;
                            }
                            return getAlgoliaResults(resultsConfig);
                        },
                        templates: source.templates
                    });
                }
            });

            sources.push({
                sourceId: 'productCategories',
                getItems() {
                    return getAlgoliaFacets({
                        searchClient,
                        transformResponse({ facetHits }) {
                            setContext({ productsFacetHits: facetHits[0] });
                            return []
                        },
                        queries: [
                            {
                                indexName: algoliaConfig.indexName + "_products",
                                facet: 'categories.level0',
                                query,
                                params: {
                                    maxFacetHits: 10,
                                }
                            },
                        ]
                    });
                },
                templates: {
                    item: () => {}
                }
            })

            return sources;
        },
        render({ elements, render, html }, root) {
            const { categories, pages, products, querySuggestionsPlugin } = elements;
            render(
                html`<div class="relative w-full flex flex-col lg:flex-row">
      ${querySuggestionsPlugin}
      <div class="w-full lg:w-2/3 lg:order-1 lg:border-l-2">${products}</div>
      <div class="w-full lg:w-1/3 px-1 pt-2 pb-10 md:px-2 md:pt-4">${categories} ${pages}</div>
      </div>`,
                root
            );
        },
        renderNoResults({ state, render,html }, root) {
            const suggestions = [];

            if (algoliaConfig.showSuggestionsOnNoResultsPage && algoliaConfig.popularQueries.length > 0) {
                algoliaConfig.popularQueries
                    .slice(0, Math.min(3, algoliaConfig.popularQueries.length))
                    .forEach(function (query) {
                        suggestions.push({ url: algoliaConfig.baseUrl + '/catalogsearch/result/?q=' + encodeURIComponent(query),  query });
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
                ${suggestions.map(({ url, query}) => html`<a class="text-sm xl:text-base text-gray-600 tracking-wide font-semibold hover:underline" href="${url}">${query}</a>`)}
                </div>` : '')
            }
            <a class="py-2 text-sm xl:text-base text-gray-600 tracking-wide font-bold hover:underline" href="${algoliaConfig.baseUrl}/catalogsearch/result/?q=__empty__">${algoliaConfig.translations.seeAll}</a>
        </div>
      </div>`, root);
        },
    }
);
