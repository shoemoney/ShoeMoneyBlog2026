@props(['wireModel' => 'content', 'content' => '', 'placeholder' => 'Start writing your post content...'])

<div
    x-data="tiptapEditor(@js($content), '{{ $wireModel }}')"
    x-init="init()"
    x-on:destroy="destroy()"
    class="tiptap-wrapper border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500"
>
    {{-- Toolbar --}}
    <div x-show="!previewMode" class="tiptap-toolbar bg-gray-50 border-b border-gray-200 px-2 py-1.5 flex flex-wrap items-center gap-0.5">

        {{-- History --}}
        <button type="button" @click="undo()" class="tiptap-btn" title="Undo">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2M3 10l4-4m-4 4l4 4"/></svg>
        </button>
        <button type="button" @click="redo()" class="tiptap-btn" title="Redo">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2m15-7l-4-4m4 4l-4 4"/></svg>
        </button>

        <span class="tiptap-divider"></span>

        {{-- Heading dropdown --}}
        <select @change="setHeading(parseInt($event.target.value))" class="tiptap-select text-xs">
            <option value="0" :selected="!isActive('heading')">Paragraph</option>
            <option value="1" :selected="isActive('heading', {level: 1})">H1</option>
            <option value="2" :selected="isActive('heading', {level: 2})">H2</option>
            <option value="3" :selected="isActive('heading', {level: 3})">H3</option>
            <option value="4" :selected="isActive('heading', {level: 4})">H4</option>
            <option value="5" :selected="isActive('heading', {level: 5})">H5</option>
            <option value="6" :selected="isActive('heading', {level: 6})">H6</option>
        </select>

        <span class="tiptap-divider"></span>

        {{-- Text formatting --}}
        <button type="button" @click="toggleBold()" :class="{'tiptap-btn-active': isActive('bold')}" class="tiptap-btn" title="Bold">
            <strong class="text-xs">B</strong>
        </button>
        <button type="button" @click="toggleItalic()" :class="{'tiptap-btn-active': isActive('italic')}" class="tiptap-btn" title="Italic">
            <em class="text-xs">I</em>
        </button>
        <button type="button" @click="toggleUnderline()" :class="{'tiptap-btn-active': isActive('underline')}" class="tiptap-btn" title="Underline">
            <span class="text-xs underline">U</span>
        </button>
        <button type="button" @click="toggleStrike()" :class="{'tiptap-btn-active': isActive('strike')}" class="tiptap-btn" title="Strikethrough">
            <span class="text-xs line-through">S</span>
        </button>
        <button type="button" @click="toggleCode()" :class="{'tiptap-btn-active': isActive('code')}" class="tiptap-btn" title="Inline Code">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
        </button>
        <button type="button" @click="toggleSubscript()" :class="{'tiptap-btn-active': isActive('subscript')}" class="tiptap-btn" title="Subscript">
            <span class="text-xs">X<sub>2</sub></span>
        </button>
        <button type="button" @click="toggleSuperscript()" :class="{'tiptap-btn-active': isActive('superscript')}" class="tiptap-btn" title="Superscript">
            <span class="text-xs">X<sup>2</sup></span>
        </button>
        <button type="button" @click="toggleHighlight()" :class="{'tiptap-btn-active': isActive('highlight')}" class="tiptap-btn" title="Highlight">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
        </button>

        <span class="tiptap-divider"></span>

        {{-- Alignment --}}
        <button type="button" @click="setTextAlign('left')" :class="{'tiptap-btn-active': isActive({textAlign: 'left'})}" class="tiptap-btn" title="Align Left">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h14"/></svg>
        </button>
        <button type="button" @click="setTextAlign('center')" :class="{'tiptap-btn-active': isActive({textAlign: 'center'})}" class="tiptap-btn" title="Align Center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M5 18h14"/></svg>
        </button>
        <button type="button" @click="setTextAlign('right')" :class="{'tiptap-btn-active': isActive({textAlign: 'right'})}" class="tiptap-btn" title="Align Right">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M6 18h14"/></svg>
        </button>

        <span class="tiptap-divider"></span>

        {{-- Lists --}}
        <button type="button" @click="toggleBulletList()" :class="{'tiptap-btn-active': isActive('bulletList')}" class="tiptap-btn" title="Bullet List">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h.01M8 6h12M4 12h.01M8 12h12M4 18h.01M8 18h12"/></svg>
        </button>
        <button type="button" @click="toggleOrderedList()" :class="{'tiptap-btn-active': isActive('orderedList')}" class="tiptap-btn" title="Ordered List">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10M7 16h10M3 8V6l2-1M3 12h2M3 18h2"/></svg>
        </button>
        <button type="button" @click="toggleTaskList()" :class="{'tiptap-btn-active': isActive('taskList')}" class="tiptap-btn" title="Task List">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
        </button>

        <span class="tiptap-divider"></span>

        {{-- Block elements --}}
        <button type="button" @click="toggleBlockquote()" :class="{'tiptap-btn-active': isActive('blockquote')}" class="tiptap-btn" title="Blockquote">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/></svg>
        </button>
        <button type="button" @click="toggleCodeBlock()" :class="{'tiptap-btn-active': isActive('codeBlock')}" class="tiptap-btn" title="Code Block">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </button>
        <button type="button" @click="setHorizontalRule()" class="tiptap-btn" title="Horizontal Rule">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"/></svg>
        </button>

        <span class="tiptap-divider"></span>

        {{-- Links & Media --}}
        <button type="button" @click="addLink()" :class="{'tiptap-btn-active': isActive('link')}" class="tiptap-btn" title="Add Link">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
        </button>
        <button type="button" @click="removeLink()" class="tiptap-btn" title="Remove Link" x-show="isActive('link')">
            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        </button>
        <button type="button" @click="addImage()" class="tiptap-btn" title="Insert Image">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </button>
        <button type="button" @click="addYoutube()" class="tiptap-btn" title="Embed YouTube">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
        </button>

        <span class="tiptap-divider"></span>

        {{-- Table --}}
        <button type="button" @click="insertTable()" class="tiptap-btn" title="Insert Table">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M10 3v18M14 3v18M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6z"/></svg>
        </button>
        <template x-if="isActive('table')">
            <div class="flex items-center gap-0.5">
                <button type="button" @click="addColumnAfter()" class="tiptap-btn text-xs" title="Add Column">+Col</button>
                <button type="button" @click="addRowAfter()" class="tiptap-btn text-xs" title="Add Row">+Row</button>
                <button type="button" @click="deleteTable()" class="tiptap-btn text-xs text-red-500" title="Delete Table">×Tbl</button>
            </div>
        </template>

        {{-- Spacer --}}
        <div class="flex-1"></div>

        {{-- Mode toggles --}}
        <button
            type="button"
            @click="rawMode = !rawMode"
            :class="{'tiptap-btn-active': rawMode}"
            class="tiptap-btn text-xs font-mono"
            title="Toggle HTML Source"
        >
            &lt;/&gt;
        </button>
        <button
            type="button"
            @click="previewMode = !previewMode"
            class="tiptap-btn text-xs"
            title="Preview"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        </button>
    </div>

    {{-- Visual Editor --}}
    <div x-show="!rawMode && !previewMode" class="tiptap-editor-area bg-white">
        <div x-ref="editorContainer" class="min-h-[400px]"></div>
    </div>

    {{-- Raw HTML Editor --}}
    <div x-show="rawMode && !previewMode" class="bg-white">
        <textarea
            x-model="rawContent"
            @input="updateRawContent()"
            class="w-full min-h-[400px] px-4 py-3 font-mono text-sm text-gray-800 bg-gray-900 text-green-400 focus:outline-none resize-y"
            spellcheck="false"
        ></textarea>
    </div>

    {{-- Preview Mode --}}
    <div x-show="previewMode" class="bg-white">
        <div class="flex items-center justify-between px-4 py-2 bg-blue-50 border-b border-blue-200">
            <span class="text-sm font-medium text-blue-700">Preview Mode</span>
            <button type="button" @click="previewMode = false" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                &larr; Back to Editor
            </button>
        </div>
        <div class="prose prose-sm sm:prose-base max-w-none px-4 py-3 min-h-[400px]" x-html="editor ? editor.getHTML() : ''"></div>
    </div>
</div>
