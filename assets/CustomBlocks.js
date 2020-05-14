
// test script execution
// alert('customblock script enqueued')

// ES5 code

var BlockName = 'seminardesk/test', // defining namespace and name of the block
    createElement = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    apiFetch = wp.apiFetch,
    ServerSideRender = wp.serverSideRender

registerBlockType (BlockName, {

    //build-in attributes
    title: 'Test',
    description: "This a Test Block for SeminarDesk", 
    icon: 'smiley',
    category: 'seminardesk',
    // keywords: [
    //   __( 'SeminarDesk' ),
    //   __( 'Events' ),
    //   __( 'Webhooks' ),
    // ],

    // custom attributes
    attribute: {
        author: {
            type: 'string',
             source: 'meta',
            meta: 'author',
        },
        text: {type: 'string'},
    },

    // custom methods are empty
    // **empty**

    // build-in methods 
    /**
     * block editor code 
     * 
     * @param {*} props 
     */
    edit: function ( props ) {
        // GET
        apiFetch( { path: '/wp/v2/posts' } ).then( sd_event => {
        console.log( sd_event );
        } )
        return (
            // getting dynamic block content from php and viewing it in block editor
            createElement( ServerSideRender, {
                block: BlockName,
                attributes: props.attributes,
            } )
        );
    },
    
    /**
     * block viewer/frontend code
     * note: all change in the save method require to be user accepted by deleting the old block and recreating the block in the editor
     * 
     * @param {*} props 
     */
    save: function(props) {return null}, // using dynamic blocks in php
})