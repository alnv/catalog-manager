Catalog Manager for Contao CMS
===============

Please visit the official [Catalog Manager][1] Site. Here you can find Catalog Manager [Demo Page][4].

## Installation

**Contao 3.5**

You can install Catalog Manager in Extension Repository. Just search for "Catalog-Manager" :)

**Contao 4.3**

- Download [Catalog Manager][2]
- Unzip and rename the folder into "catalog-manager"
- Copy "catalog-manager" folder into "system/modules/" directory
- Open "app/AppKernel.php" and put this code on the end of the $bundles array => `new Contao\CoreBundle\HttpKernel\Bundle\ContaoModuleBundle( ('catalog-manager'), $this->getRootDir() )`
- Clean cache `./bin/console cache:clear --env=prod`
- Open "contao/install" in your browser and create all tables and columns

Do you have some issues? Contact me: https://www.alexandernaumov.de

[1]: https://catalog-manager.alexandernaumov.de
[2]: https://github.com/alnv/catalog-manager/archive/v1.2.9.tar.gz
[3]: https://catalog-manager.alexandernaumov.de
[4]: http://catalog-manager-demo.alexandernaumov.de