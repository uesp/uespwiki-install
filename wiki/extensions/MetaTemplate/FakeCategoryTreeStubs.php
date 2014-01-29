<?php
/* This file simply contains derived class stubs, used by MetaTemplateCategoryPage

   It is necessary because MetaTemplateCategoryPage is set up to extend CategoryTreeCategoryPage, thus
   allowing both extensions to coexist on the same wiki.  But MetaTemplate doesn't really require
   CategoryTree to be installed, so this is the workaround that makes the extension happy when
   CategoryTree isn't present */

class CategoryTreeCategoryPage extends CategoryPage {
}

class CategoryTreeCategoryViewer extends CategoryViewer {
}
