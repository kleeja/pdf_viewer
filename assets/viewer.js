/**
 * The PDF viewer of the download page, built on the viewer components of PDF.js (mozilla.github.io/pdf.js):
 * - PDF.js is loaded from the CDN in init.php (data-cdn), its worker and its data files from the same version
 * - the toolbar moves through the pages and zooms, the pages scroll in their own box
 * - full screen shows the viewer alone, presentation mode shows one page at a time, fitted to the screen,
 *   and the arrow keys, Space, Home, End, a click or the mouse wheel move through the pages
 * - the scripts of a PDF file never run, and its links open in a new tab
 * - a file that can't be shown (damaged, protected with a password, or PDF.js couldn't be loaded) leaves a message,
 *   the download button of the page is still there
 */

// the zoom values that PDF.js computes from the size of the box, they change when the box does
const PRESET_SCALES = ['auto', 'page-actual', 'page-fit', 'page-width'];

// the smallest and the largest zoom of PDF.js, MIN_SCALE and MAX_SCALE of its viewer
const MIN_SCALE = 0.1;
const MAX_SCALE = 10;

// PresentationModeState.NORMAL and .FULLSCREEN of PDF.js, its components don't export them
const PRESENTATION_NORMAL = 1;
const PRESENTATION_FULLSCREEN = 3;

for (const root of document.querySelectorAll('[data-kj-pdf]')) {
    start(root);
}

async function start(root) {
    const cdn = root.dataset.cdn;
    const ui = {
        box: root.querySelector('[data-kj-pdf-box]'),
        stage: root.querySelector('[data-kj-pdf-stage]'),
        container: root.querySelector('[data-kj-pdf-container]'),
        status: root.querySelector('[data-kj-pdf-status]'),
        message: root.querySelector('[data-kj-pdf-message]'),
        page: root.querySelector('[data-kj-pdf-page]'),
        count: root.querySelector('[data-kj-pdf-count]'),
        scale: root.querySelector('[data-kj-pdf-scale]'),
        custom: root.querySelector('[data-kj-pdf-custom]'),
        screen: root.querySelector('[data-kj-pdf-screen]'),
        actions: Object.fromEntries(
            [...root.querySelectorAll('[data-kj-pdf-action]')].map((button) => [button.dataset.kjPdfAction, button]),
        ),
    };

    let pdfjs, components;

    try {
        // the components read the library from globalThis.pdfjsLib, which the library sets, so it comes first
        pdfjs = await import(cdn + 'build/pdf.min.mjs');
        components = await import(cdn + 'web/pdf_viewer.mjs');
    } catch (error) {
        console.error('pdf_viewer: PDF.js could not be loaded', error);
        fail(root, ui, root.dataset.error);

        return;
    }

    // a worker of another origin is wrapped by PDF.js in a module of this page
    pdfjs.GlobalWorkerOptions.workerSrc = cdn + 'build/pdf.worker.min.mjs';

    const eventBus = new components.EventBus();
    const linkService = new components.PDFLinkService({
        eventBus,
        externalLinkTarget: components.LinkTarget.BLANK,
        externalLinkRel: 'noopener noreferrer nofollow',
    });
    const viewer = new components.PDFViewer({
        container: ui.container,
        eventBus,
        linkService,
        imageResourcesPath: cdn + 'web/images/',
    });

    linkService.setViewer(viewer);

    eventBus.on('pagesinit', () => {
        viewer.currentScaleValue = 'auto';
        ui.count.textContent = String(viewer.pagesCount);
        ui.page.max = String(viewer.pagesCount);
        ui.status.hidden = true;

        for (const control of [ui.page, ui.scale, ...Object.values(ui.actions)]) {
            control.disabled = false;
        }

        updatePage(ui, viewer);
        updateScale(ui, viewer);
    });

    eventBus.on('pagechanging', () => updatePage(ui, viewer));
    eventBus.on('scalechanging', () => updateScale(ui, viewer));

    bindToolbar(ui, viewer);
    bindScreen(ui, viewer, eventBus, components.ScrollMode);
    keepPresetScale(ui, viewer);

    const task = pdfjs.getDocument({
        url: root.dataset.src,
        cMapUrl: cdn + 'cmaps/',
        cMapPacked: true,
        standardFontDataUrl: cdn + 'standard_fonts/',
        wasmUrl: cdn + 'wasm/',
        iccUrl: cdn + 'iccs/',
    });

    try {
        const pdfDocument = await task.promise;

        viewer.setDocument(pdfDocument);
        linkService.setDocument(pdfDocument);
    } catch (error) {
        console.error('pdf_viewer: the file could not be opened', error);
        fail(root, ui, error?.name === 'PasswordException' ? root.dataset.errorPassword : root.dataset.error);
    }
}

// the message instead of the pages, the toolbar stays disabled
function fail(root, ui, message) {
    root.classList.add('is-failed');
    ui.message.textContent = message;
    ui.status.hidden = false;
}

function bindToolbar(ui, viewer) {
    ui.actions.previous.addEventListener('click', () => viewer.previousPage());
    ui.actions.next.addEventListener('click', () => viewer.nextPage());
    ui.actions['zoom-in'].addEventListener('click', () => viewer.increaseScale());
    ui.actions['zoom-out'].addEventListener('click', () => viewer.decreaseScale());

    // a page that doesn't exist puts the current one back in the box
    ui.page.addEventListener('change', () => {
        const number = Number.parseInt(ui.page.value, 10);

        if (number >= 1 && number <= viewer.pagesCount) {
            viewer.currentPageNumber = number;
        }

        updatePage(ui, viewer);
    });

    ui.page.addEventListener('focus', () => ui.page.select());

    ui.scale.addEventListener('change', () => {
        if (ui.scale.value !== 'custom') {
            viewer.currentScaleValue = ui.scale.value;
        }
    });
}

function updatePage(ui, viewer) {
    const number = viewer.currentPageNumber;

    ui.page.value = String(number);
    ui.actions.previous.disabled = number <= 1;
    ui.actions.next.disabled = number >= viewer.pagesCount;
}

// a zoom that isn't in the list, after the zoom buttons, is shown in the hidden option
function updateScale(ui, viewer) {
    const value = viewer.currentScaleValue;
    const scale = viewer.currentScale;
    const option = [...ui.scale.options].find((item) => item !== ui.custom && Number(item.value) === Number(value));

    if (PRESET_SCALES.includes(value) || option) {
        ui.scale.value = PRESET_SCALES.includes(value) ? value : option.value;
        ui.custom.hidden = true;
    } else {
        ui.custom.textContent = Math.round(scale * 100) + '%';
        ui.custom.hidden = false;
        ui.scale.value = 'custom';
    }

    ui.actions['zoom-out'].disabled = scale <= MIN_SCALE;
    ui.actions['zoom-in'].disabled = scale >= MAX_SCALE;
}

// PDF.js computes automatic zoom, actual size, fit page and fit width once, so they are computed again
// when the box changes its size: the window, the screen of a phone that turns, full screen
function keepPresetScale(ui, viewer) {
    let frame = 0;

    new ResizeObserver(() => {
        cancelAnimationFrame(frame);

        frame = requestAnimationFrame(() => {
            const value = viewer.currentScaleValue;

            if (viewer.pagesCount && PRESET_SCALES.includes(value)) {
                viewer.currentScaleValue = value;
            }
        });
    }).observe(ui.container);
}

// full screen shows the box with its toolbar, presentation mode the pages alone, one at a time.
// Phones that can't show an element in full screen (iPhone) don't get the buttons
function bindScreen(ui, viewer, eventBus, ScrollMode) {
    if (!document.fullscreenEnabled) {
        return;
    }

    ui.screen.hidden = false;

    let saved = null;
    let lastWheel = 0;

    const present = (state) => {
        viewer.presentationModeState = state;
        eventBus.dispatch('presentationmodechanged', { source: null, state });
    };

    ui.actions.fullscreen.addEventListener('click', () => {
        if (document.fullscreenElement === ui.box) {
            document.exitFullscreen();
        } else {
            ui.box.requestFullscreen().catch(() => {});
        }
    });

    ui.actions.presentation.addEventListener('click', () => {
        ui.stage.requestFullscreen().catch(() => {});
    });

    document.addEventListener('fullscreenchange', () => {
        const element = document.fullscreenElement;
        const inBox = element === ui.box;

        ui.actions.fullscreen.setAttribute('aria-pressed', String(inBox));
        ui.actions.fullscreen.querySelector('i').className = 'fa-solid ' + (inBox ? 'fa-compress' : 'fa-expand');

        if (element === ui.stage && !saved) {
            // the page, the zoom and the scrolling are given back when presentation mode ends
            saved = {
                page: viewer.currentPageNumber,
                scale: viewer.currentScaleValue,
                scrollMode: viewer.scrollMode,
            };

            ui.container.classList.add('pdfPresentationMode');
            present(PRESENTATION_FULLSCREEN);
            viewer.scrollMode = ScrollMode.PAGE;
            ui.container.focus({ preventScroll: true });

            // after the box has taken the size of the screen
            requestAnimationFrame(() => {
                viewer.currentPageNumber = saved.page;
                viewer.currentScaleValue = 'page-fit';
            });
        } else if (element !== ui.stage && saved) {
            // the page that presentation mode ended on stays in view
            const page = viewer.currentPageNumber;
            const { scale, scrollMode } = saved;

            saved = null;
            ui.container.classList.remove('pdfPresentationMode');
            present(PRESENTATION_NORMAL);
            viewer.scrollMode = scrollMode;
            viewer.currentScaleValue = scale;
            viewer.currentPageNumber = page;
        }
    });

    // in presentation mode, the keys and the clicks move through the pages, as in the viewer of Firefox.
    // The stage is all there is on the screen then, and a click on a page leaves the focus on the body,
    // so the keys are read from the whole document
    document.addEventListener('keydown', (event) => {
        if (
            !saved ||
            event.altKey ||
            event.ctrlKey ||
            event.metaKey ||
            event.target.closest?.('input, select, textarea')
        ) {
            return;
        }

        const moves = {
            ArrowRight: 'next',
            ArrowDown: 'next',
            PageDown: 'next',
            Enter: 'next',
            ' ': event.shiftKey ? 'previous' : 'next',
            ArrowLeft: 'previous',
            ArrowUp: 'previous',
            PageUp: 'previous',
            Backspace: 'previous',
            Home: 'first',
            End: 'last',
        };
        const move = moves[event.key];

        if (!move) {
            return;
        }

        event.preventDefault();
        turnPage(viewer, move);
    });

    ui.stage.addEventListener('click', (event) => {
        // the links and the fields of the file keep their own click
        if (saved && event.button === 0 && !event.target.closest('a, button, input, select, textarea')) {
            turnPage(viewer, event.shiftKey ? 'previous' : 'next');
        }
    });

    ui.stage.addEventListener(
        'wheel',
        (event) => {
            if (!saved || event.ctrlKey) {
                return;
            }

            event.preventDefault();

            // a touchpad sends many small events for one swipe, one page turns for them
            if (Math.abs(event.deltaY) < 4 || event.timeStamp - lastWheel < 300) {
                return;
            }

            lastWheel = event.timeStamp;
            turnPage(viewer, event.deltaY > 0 ? 'next' : 'previous');
        },
        { passive: false },
    );
}

function turnPage(viewer, move) {
    if (move === 'next') {
        viewer.nextPage();
    } else if (move === 'previous') {
        viewer.previousPage();
    } else {
        viewer.currentPageNumber = move === 'first' ? 1 : viewer.pagesCount;
    }
}
