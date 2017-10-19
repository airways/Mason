# Mason

Please see the License section at the end of this file. By using ProForm, you are agreeing to this license.

Mason is a "content block" element design to work with the Content Elements fieldtype in ExpressionEngine. You can use Mason to create reusable blocks of fields/elements which can be placed on to a page as many times as needed, in any order, and which remain bound together as a single piece of data.

Beta
----

Please post details on the projects you are/will use Mason for, what capabilities you see as useful in the future, and any bug reports that you may have. When posting a bug report, please include screenshots and steps to reproduce the bug. Thanks for your help!

The beta group is located here: https://groups.google.com/forum/#!forum/mason-beta

Installation
------------

1. Move the third_party/content_elements/elements/mason folder
2. Move the third_party/mason folder
3. Move the themes/third_party/content_elements/elements/mason folder

When installing mason, be sure to enable the Mason extension in order for proper handling of data.
Elements themselves do not have any installation step.

You will want to remove the predefined Mason (Content Block) element from any of your Content Elements
fields since this is a very generic name for a content block. Instead custom Mason elements in each
CE field with unique names appropriate to each block type.

Usage
-----

Using Mason in the CP is fairly straight forward - create a new Content Elements field, then add
your Mason definitions containing the content blocks you wish to use in that field. At this point
you can use those content blocks on that field as many times as you want within the Publishing
interface of ExpressionEngine.

Note that when creating a new subelement within a Mason block, or changing a subelement's type,
you will need to save the field twice in order to apply the new settings for that subelement (settings
for each element won't appear until it's saved once). The extension should redirect you back to the
field settings screen with a note to this effect, and with the Mason block you were editing expanded
and visible, so this should be very easy to remember to do.

Inside your templates, you will want to extract content from a Mason block. To do so, you start with a
normal Content Elements loop (if you are making using of non-Mason elements), then new blocks of code
for each of your content blocks:

{body}
    {wysiwyg}
        {value}
    {/wysiwyg}
    
    ... etc ...
    
    {mason}
        {if block_name == "Article Intro"} {!-- repeat this section, changing the name for each custom block --}
            {!-- subelements are addressed by name like so (instead of by the element type tag as in a normal CE field): --}
            <p>{summary}{value}{/summary}</p>
            {!-- if you just want the value, you can write the tag by itself: --}
            <p><b>By {byline}</b></p>
            {!-- you can only use one style for each subelement or the parser will get confused --}
            {hero_images}
                {if images}
            		{images}
            			<img src="{thumb}" /><br />
            		{/images}
            	{/if}
        	{/hero_images}
        {/if}
    {/mason}
{/body}

## License

Copyright (c)2009, 2010, 2011, 2012, 2013, 2014, 2015, 2016.
Isaac Raway and MetaSushi, LLC. All rights reserved.

You may use this software under a commercial license, if you have one,
or under the GPL v3 contained in LICENSE, in which case you MUST
comply with all GPL requirements.

This source is commercial software. Use of this software requires a
site license for each domain it is used on. Use of this software or any
of its source code without express written permission in the form of
a purchased commercial or other license is prohibited.

THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
PARTICULAR PURPOSE.

As part of the license agreement for this software, all modifications
to this source must be submitted to the original author for review and
possible inclusion in future releases. No compensation will be provided
for patches, although where possible we will attribute each contribution
in file revision notes. Submitting such modifications constitutes
assignment of copyright to the original author (Isaac Raway and
MetaSushi, LLC) for such modifications. If you do not wish to assign
copyright to the original author, your license to  use and modify this
source is null and void. Use of this software constitutes your agreement
to this clause.
