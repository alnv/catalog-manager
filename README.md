Catalog Manager for Contao CMS
===============

Here you find [Catalog Manager Documentation][3] ( German only )

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

**Lizenzvereinbarung**

Die Contao Erweiterung "Catalog-Manager" wird kostenpflichtig angeboten. Mit dem Erwerb einer Lizenz darf die Erweiterung verwendet werden. Die Nutzungslizenz ist an eine einzige Contao Installationen gebunden. Es muss pro Contao Installation, eine neue Lizenz erworben werden. **Für Testzwecke kann der Catalog Manager kostenfrei installiert werden**, sofern die Software auf einer lokalen Webumgebungen liegt. Die Lizenz erlaubt nur die Nutzung der Software jegliche Veränderung am Quellcode ist verboten. Die Software und der dazugehörende Quellcode sind urheberrechtlich geschützt.
Alle zukünftig erscheinenden Updates ( Bugfixes und Sicherheitsupdates ) sind kostenlos. Die Software wird ohne jede ausdrückliche oder implizierte Garantie bereitgestellt.

[1]: https://www.alexandernaumov.de/blog/f-modul-2-0-aka-catalog-manager
[2]: https://github.com/alnv/catalog-manager/archive/v1.0-beta.10.tar.gz
[3]: https://github.com/alnv/catalog-manager/wiki