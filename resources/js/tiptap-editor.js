import { Editor } from '@tiptap/core'
import { StarterKit } from '@tiptap/starter-kit'
import { Link } from '@tiptap/extension-link'
import { Image } from '@tiptap/extension-image'
import { Underline } from '@tiptap/extension-underline'
import { TextAlign } from '@tiptap/extension-text-align'
import { Placeholder } from '@tiptap/extension-placeholder'
import { CodeBlockLowlight } from '@tiptap/extension-code-block-lowlight'
import { TextStyle } from '@tiptap/extension-text-style'
import { Color } from '@tiptap/extension-color'
import { Highlight } from '@tiptap/extension-highlight'
import { Table } from '@tiptap/extension-table'
import { TableRow } from '@tiptap/extension-table-row'
import { TableCell } from '@tiptap/extension-table-cell'
import { TableHeader } from '@tiptap/extension-table-header'
import { Youtube } from '@tiptap/extension-youtube'
import { Subscript } from '@tiptap/extension-subscript'
import { Superscript } from '@tiptap/extension-superscript'
import { TaskList } from '@tiptap/extension-task-list'
import { TaskItem } from '@tiptap/extension-task-item'
import { Typography } from '@tiptap/extension-typography'
import { common, createLowlight } from 'lowlight'

const lowlight = createLowlight(common)

export default function tiptapEditor(content, wireModelName) {
    return {
        editor: null,
        rawMode: false,
        rawContent: '',
        previewMode: false,

        init() {
            const self = this

            this.editor = new Editor({
                element: this.$refs.editorContainer,
                extensions: [
                    StarterKit.configure({
                        codeBlock: false, // using lowlight version
                        heading: { levels: [1, 2, 3, 4, 5, 6] },
                    }),
                    Link.configure({
                        openOnClick: false,
                        HTMLAttributes: { class: 'text-blue-600 underline' },
                    }),
                    Image.configure({
                        HTMLAttributes: { class: 'max-w-full rounded-lg' },
                    }),
                    Underline,
                    TextAlign.configure({
                        types: ['heading', 'paragraph'],
                    }),
                    Placeholder.configure({
                        placeholder: 'Start writing your post content...',
                    }),
                    CodeBlockLowlight.configure({ lowlight }),
                    TextStyle,
                    Color,
                    Highlight.configure({ multicolor: true }),
                    Table.configure({ resizable: true }),
                    TableRow,
                    TableCell,
                    TableHeader,
                    Youtube.configure({
                        HTMLAttributes: { class: 'w-full aspect-video rounded-lg' },
                    }),
                    Subscript,
                    Superscript,
                    TaskList,
                    TaskItem.configure({ nested: true }),
                    Typography,
                ],
                content: content || '',
                editorProps: {
                    attributes: {
                        class: 'prose prose-sm sm:prose-base max-w-none focus:outline-none min-h-[300px] px-4 py-3',
                    },
                },
                onUpdate({ editor }) {
                    const html = editor.getHTML()
                    // Update Livewire property
                    if (wireModelName && self.$wire) {
                        self.$wire.set(wireModelName, html)
                    }
                },
            })

            this.rawContent = content || ''

            // Watch for external content changes (e.g. Livewire updates)
            this.$watch('rawMode', (val) => {
                if (!val && this.editor) {
                    // Switching back to visual mode - apply raw edits
                    this.editor.commands.setContent(this.rawContent, false)
                    if (wireModelName && this.$wire) {
                        this.$wire.set(wireModelName, this.rawContent)
                    }
                } else if (val && this.editor) {
                    // Switching to raw mode - grab current HTML
                    this.rawContent = this.editor.getHTML()
                }
            })
        },

        destroy() {
            if (this.editor) {
                this.editor.destroy()
            }
        },

        // Toolbar actions
        toggleBold() { this.editor.chain().focus().toggleBold().run() },
        toggleItalic() { this.editor.chain().focus().toggleItalic().run() },
        toggleUnderline() { this.editor.chain().focus().toggleUnderline().run() },
        toggleStrike() { this.editor.chain().focus().toggleStrike().run() },
        toggleCode() { this.editor.chain().focus().toggleCode().run() },
        toggleCodeBlock() { this.editor.chain().focus().toggleCodeBlock().run() },
        toggleBlockquote() { this.editor.chain().focus().toggleBlockquote().run() },
        toggleBulletList() { this.editor.chain().focus().toggleBulletList().run() },
        toggleOrderedList() { this.editor.chain().focus().toggleOrderedList().run() },
        toggleTaskList() { this.editor.chain().focus().toggleTaskList().run() },
        toggleHighlight() { this.editor.chain().focus().toggleHighlight().run() },
        toggleSubscript() { this.editor.chain().focus().toggleSubscript().run() },
        toggleSuperscript() { this.editor.chain().focus().toggleSuperscript().run() },
        setHorizontalRule() { this.editor.chain().focus().setHorizontalRule().run() },
        undo() { this.editor.chain().focus().undo().run() },
        redo() { this.editor.chain().focus().redo().run() },

        setHeading(level) {
            if (level === 0) {
                this.editor.chain().focus().setParagraph().run()
            } else {
                this.editor.chain().focus().toggleHeading({ level }).run()
            }
        },

        setTextAlign(align) {
            this.editor.chain().focus().setTextAlign(align).run()
        },

        addLink() {
            const url = prompt('Enter URL:')
            if (url) {
                this.editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
            }
        },

        removeLink() {
            this.editor.chain().focus().unsetLink().run()
        },

        addImage() {
            const url = prompt('Enter image URL:')
            if (url) {
                this.editor.chain().focus().setImage({ src: url }).run()
            }
        },

        addYoutube() {
            const url = prompt('Enter YouTube URL:')
            if (url) {
                this.editor.chain().focus().setYoutubeVideo({ src: url }).run()
            }
        },

        insertTable() {
            this.editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()
        },

        addColumnAfter() { this.editor.chain().focus().addColumnAfter().run() },
        addRowAfter() { this.editor.chain().focus().addRowAfter().run() },
        deleteTable() { this.editor.chain().focus().deleteTable().run() },

        // Check if a mark/node is active
        isActive(name, attrs = {}) {
            return this.editor?.isActive(name, attrs) ?? false
        },

        updateRawContent() {
            // Called on raw textarea input
            if (wireModelName && this.$wire) {
                this.$wire.set(wireModelName, this.rawContent)
            }
        },
    }
}

// Register globally for Alpine
window.tiptapEditor = tiptapEditor
