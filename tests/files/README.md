You can access those files in every test, which extends `Webforge\Common\TestCase` with `$this->getFile('subDir/thename.txt');`
you can get directories with: `$this->getTestDirectory('subDir/')`