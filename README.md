<!-- Improved compatibility of back to top link: See: https://github.com/othneildrew/Best-README-Template/pull/73 -->
<a name="readme-top"></a>

<!-- PROJECT LOGO -->
<br />
<div>
  <h3 align="center">Magento 2 Typesense Adapter Module</h3>

  <p align="left">
     <br />
    <h4 align="left">This is currently just a proof of concept!</h3>
    <br />
    It's an Adapter Client for the main Algolia Magento 2 module.
    <br />
    We let the existing Open Source Algolia module handle indexing, queues etc, when the data is ready to be indexed it's pushed Typesense.
    <br />
    Why reinvent the wheel?
  </p>
</div>



<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#roadmap">Roadmap</a></li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#license">License</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><a href="#acknowledgments">Acknowledgments</a></li>
  </ol>
</details>

<!-- GETTING STARTED -->
## Getting Started

Composer install this module, it will include the Algolia module as a dependancy. 

### Installation

   ```shell
   composer require develodesign/magento2-module-typesense
   ```
   
   Add Typesene Configuration
   
   System Config -> Type Sense -> Settings -> General

<p align="right">(<a href="#readme-top">back to top</a>)</p>


<!-- ROADMAP -->
## Roadmap

- [x] Create Basic module and Config
- [x] Index Product Data
- [ ] Index Categories
- [ ] Admin Facets
- [ ] Loads More ....

<p align="right">(<a href="#readme-top">back to top</a>)</p>


<!-- CONTRIBUTING -->
## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- LICENSE -->
## License

Distributed under the NU General Public License. See `LICENSE.txt` for more information.

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- CONTACT -->
## Contact
Luke Collymore - [@lukecollymore](https://twitter.com/lukecollymore) - luke@develodesign.co.uk

@Nathan McBride - nathan@brideo.co.uk

Project Link: [https://github.com/develodesign/magento2-module-typesense](https://github.com/develodesign/magento2-module-typesense)

<p align="right">(<a href="#readme-top">back to top</a>)</p>


<!-- ACKNOWLEDGMENTS -->
## Acknowledgments
Algolia for creating a great product indexing and search configuration module
* [Algolia Open Source Module](https://github.com/algolia/algoliasearch-magento-2)
* [Best-README-Template](https://github.com/othneildrew/Best-README-Template)

<p align="right">(<a href="#readme-top">back to top</a>)</p>
