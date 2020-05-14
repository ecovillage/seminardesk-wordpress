// test script execution
// alert('customblock script enqueued')

// .babelrc.js
// module.exports = {
//   plugins: [
//       '@wordpress/babel-plugin-import-jsx-pragma',
//       '@babel/plugin-transform-react-jsx',
//   ],
// };

const { apiFetch } = wp.apiFetch;
const { createElement } = wp.element;

wp.blocks.registerBlockType('seminardesk/test', {

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
    edit: function(props) {
      // GET
      wp.apiFetch( { path: '/wp/v2/posts' } ).then( sd_event => {
        console.log( sd_event );
      } )
      return createElement(
        'p', 
        null, 
        createElement(
          "strong", 
          null,
          'Space Holder - SeminarDesk Test Block - Space Holder',
      ));
      }, 
    
    /**
     * block viewer/frontend code
     * note: all change in the save method require to be user accepted by deleting the old block and recreating the block in the editor
     * 
     * @param {*} props 
     */
    save: function(props) {return null}, // implanting dynamic blocks with php code
})