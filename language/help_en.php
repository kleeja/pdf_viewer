<?php
//
// pdf_viewer, its guide on the help page of the control panel
// English
//
// The guide is built by the admin_help_guides hook in init.php, the texts here can have HTML.
//  - a list is numbered: PDF_VIEWER_HELP_TIP_1, PDF_VIEWER_HELP_TIP_2 ... it ends at the first missing number,
//    so an item can be added or removed without touching the code
//  - PDF_VIEWER_HELP_{LIST}_TITLE is the title of the list, a list without it takes the title that Kleeja gives its type
//  - questions and answers are PDF_VIEWER_HELP_FAQ_Q_1 and PDF_VIEWER_HELP_FAQ_A_1
//  - names in <strong> are written as the control panel and the viewer show them: the words of Kleeja are in lang/en/,
//    the words of the viewer in pdf_viewer_translations() of init.php
//

return [
    'PDF_VIEWER_HELP_TITLE' => 'PDF Viewer',
    'PDF_VIEWER_HELP_INTRO' =>
        'Let visitors read a PDF file right on its download page, before they decide to download it. The viewer is built on PDF.js, the open-source PDF reader that Mozilla develops for Firefox, and needs no setup.',

    //
    // what the plugin does
    //
    'PDF_VIEWER_HELP_FEATURE_1' =>
        'Shows the document under the file details on the download page of every PDF file, in the Bootstrap and Default styles and in the styles based on them.',
    'PDF_VIEWER_HELP_FEATURE_2' =>
        'A toolbar to move through the pages, jump to a page by its number, zoom in and out, or choose <strong>Automatic zoom</strong>, <strong>Actual size</strong>, <strong>Fit page</strong>, <strong>Fit width</strong> or a set zoom from 50% to 200%.',
    'PDF_VIEWER_HELP_FEATURE_3' =>
        '<strong>Full screen</strong> shows the viewer alone. <strong>Presentation mode</strong> shows one page at a time on the whole screen: the arrow keys, Space, Home, End, a click or the mouse wheel move through the pages, and Esc ends it.',
    'PDF_VIEWER_HELP_FEATURE_4' =>
        'Visitors can select and copy the text of the document. Links in the document open in a new tab, and the scripts that a PDF file may contain never run.',
    'PDF_VIEWER_HELP_FEATURE_5' =>
        'Large files open fast: the viewer reads the pages it shows first, as long as your web server answers range requests, as Apache and Nginx do.',
    'PDF_VIEWER_HELP_FEATURE_6' =>
        'Follows the language, the direction and the light or dark mode of the page. The download button of the page stays as it is.',

    //
    // how to use it
    //
    'PDF_VIEWER_HELP_STEP_TITLE' => 'Show your first PDF file',
    'PDF_VIEWER_HELP_STEP_1' =>
        'Make sure that your groups may upload PDF files: open <strong>Users & Groups</strong>, select <strong>Files\' Extensions Settings</strong> on each group, and add <code>pdf</code> if it is missing.',
    'PDF_VIEWER_HELP_STEP_2' =>
        'Open <strong>Settings</strong>, then <strong>Upload settings</strong>, and make sure that <code>pdf</code> isn\'t listed in <strong>Live Extensions (No waiting page)</strong>. Those files skip the download page, so they skip the viewer too.',
    'PDF_VIEWER_HELP_STEP_3' =>
        'Upload a PDF file, then open its download page. The viewer appears under the file details.',

    //
    // common questions
    //
    'PDF_VIEWER_HELP_FAQ_Q_1' => 'The viewer doesn\'t appear on the download page of a PDF file. Why?',
    'PDF_VIEWER_HELP_FAQ_A_1' =>
        'The viewer appears only for files whose extension is <code>pdf</code>, and only on their download page. Check that <code>pdf</code> isn\'t in <strong>Live Extensions (No waiting page)</strong>, since those files never show that page. If your style has its own download template, it must keep the markup that the plugin adds; the Bootstrap and Default styles do.',
    'PDF_VIEWER_HELP_FAQ_Q_2' => 'The viewer says that the file can\'t be previewed. Why?',
    'PDF_VIEWER_HELP_FAQ_A_2' =>
        'Either the browser couldn\'t load PDF.js from jsDelivr (a firewall, a content blocker or a network without internet access), or it couldn\'t read the file (the file is damaged, or the web server blocks direct access to the upload folder). Very old browsers can\'t run the viewer either. The console of the browser\'s developer tools tells which, and visitors can still download the file.',
    'PDF_VIEWER_HELP_FAQ_Q_3' => 'What about files protected with a password?',
    'PDF_VIEWER_HELP_FAQ_A_3' =>
        'The viewer doesn\'t ask for passwords. It tells visitors that the file is protected, and they open it in their own PDF reader after they download it.',
    'PDF_VIEWER_HELP_FAQ_Q_4' => 'Does reading a file in the viewer count as a download?',
    'PDF_VIEWER_HELP_FAQ_A_4' =>
        'No. The viewer reads the file straight from the upload folder, so it neither counts a download nor waits for the <strong>Waiting period</strong> of the group. The download button of the page still does both.',
    'PDF_VIEWER_HELP_FAQ_Q_5' => 'My files are stored on another server by a plugin. Does the viewer work with them?',
    'PDF_VIEWER_HELP_FAQ_A_5' =>
        'Yes, if that server lets your site read the files: it must send the <code>Access-Control-Allow-Origin</code> header (CORS) with your site\'s address, and allow range requests for large files.',

    //
    // beside the guide
    //
    'PDF_VIEWER_HELP_TIP_1' =>
        'The viewer helps most on sites that share documents, books or forms: visitors check that a file is the one they want before they download it.',
    'PDF_VIEWER_HELP_TIP_2' =>
        'The viewer is loaded only on the download pages of PDF files, so the other pages of your site stay as light as before.',

    'PDF_VIEWER_HELP_WARNING_1' =>
        'The viewer reads each file at its direct address in the upload folder. A web server rule that blocks direct access to that folder, such as <code>deny all;</code> or <code>internal;</code> on Nginx, blocks the viewer too.',
    'PDF_VIEWER_HELP_WARNING_2' =>
        'PDF.js is loaded from the jsDelivr CDN. Visitors whose network blocks jsDelivr see a message instead of the document.',
];
