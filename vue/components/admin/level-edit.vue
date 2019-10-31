<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-body(v-if="data === null || categories === null")
            loadingAlert(:message="strings.admin_level_loading")
        template(v-else)
            .uk-card-body
                form.uk-form-stacked
                    h3(v-if="editing") {{ strings.admin_level_title_edit | stringParams(data.position + 1) }}
                    h3(v-else) {{ strings.admin_level_title_add | stringParams(data.position + 1) }}

                    vk-grid(matched).uk-grid-divider
                        div.uk-width-1-2
                            .uk-margin-small
                                label.uk-form-label {{ strings.admin_level_lbl_name }}
                                .uk-form-controls
                                    input.uk-input(v-model="data.name", :placeholder="strings.admin_level_lbl_name")
                            .uk-margin-small
                                label.uk-form-label {{ strings.admin_level_lbl_bgcolor }}
                                .uk-form-controls
                                    input.uk-input(v-model="data.bgcolor", :placeholder="strings.admin_level_lbl_bgcolor")
                                    i(v-html="strings.admin_level_lbl_bgcolor_help")
                            .uk-margin-small
                                label.uk-form-label {{ strings.admin_level_lbl_fgcolor }}
                                .uk-form-controls
                                    input.uk-input(v-model="data.fgcolor", :placeholder="strings.admin_level_lbl_fgcolor")
                                    i(v-html="strings.admin_level_lbl_fgcolor_help")
                            .uk-margin-small
                                label.uk-form-label {{ strings.admin_level_lbl_image }}
                                .uk-form-controls
                                    input.uk-input(type="file", @change="onImageSelected")
                                    span(v-if="imageFilename", v-html="stringParams(strings.admin_level_lbl_image_provided, imageFilename)")
                        div.uk-width-1-2
                            level(:level="data", :strings="strings")

                    h3.uk-margin-large-top {{ strings.admin_level_lbl_categories }}
                    .uk-margin-small(v-for="(category, index) in categories", :key="index")
                        label.uk-form-label {{ strings.admin_level_lbl_category | stringParams(index + 1) }}
                        .uk-form-controls
                            .uk-flex
                                select.uk-select(v-model="category.mdl_category")
                                    option(:disabled="true", value="") {{ strings.admin_level_lbl_category_please_select }}
                                    option(v-for="mdl_category in mdl_categories", :key="mdl_category.category_id",
                                        v-bind:value="mdl_category.category_id", :disabled="!mdl_category.category_id",
                                        v-html="mdl_category.category_name")
                                button.btn.btn-default(type="button", @click="removeCategory(index)")
                                    v-icon(name="trash")
                    btnAdd(@click="createCategory", align="left")
            .uk-card-footer.uk-text-right
                button.btn.btn-primary(@click="save()", :disabled="saving")
                    v-icon(name="save").uk-margin-small-right
                    span {{ strings.admin_btn_save }}
                button.btn.btn-default(@click="goToLevelList()", :disabled="saving").uk-margin-small-left
                    v-icon(name="ban").uk-margin-small-right
                    span {{ strings.admin_btn_cancel }}
                .uk-alert.uk-alert-primary.uk-text-center(uk-alert, v-if="saving")
                    p
                        span {{ strings.admin_level_msg_saving }}
                        span._loader
                            span
                            span
                            span

</template>

<script>
    import {mapActions, mapState} from 'vuex';
    import mixins from '../../mixins';
    import _ from 'lodash';
    import loadingAlert from "../helper/loading-alert";
    import btnAdd from './btn-add';
    import VkNotification from "vuikit/src/library/notification/components/notification";
    import Level from "../helper/level";

    export default {
        mixins: [mixins],
        props: {
            level: Object
        },
        data() {
            return {
                data: null,
                categories: null,
                saving: false,
                imageMimetype: null,
                imageContent: null,
                imageFilename: null,
            }
        },
        computed: {
            ...mapState([
                'strings',
                'game',
                'levels',
                'levelCategories',
                'mdl_categories'
            ]),
            editing() {
                return this.level !== null && this.categories !== null;
            },
            selectedCategories() {
                return _.filter(this.categories, function (category) {
                    return !!category.mdl_category;
                });
            },
        },
        methods: {
            ...mapActions([
                'fetchLevelCategories',
                'fetchMdlCategories',
                'saveLevel'
            ]),
            initLevelData(level) {
                if (level === null) {
                    this.data = {
                        id: null,
                        position: this.levels.length,
                        game: this.game.id,
                        name: '',
                        bgcolor: '',
                        fgcolor: '',
                        image: '',
                        imageurl: '',
                        imgmimetype: null,
                        imgcontent: null,
                    };
                    this.categories = [];
                } else {
                    this.data = level;
                    this.fetchLevelCategories({
                        levelid: level.id,
                    });
                }
            },
            removeCategory(index) {
                this.categories.splice(index, 1);
            },
            createCategory() {
                this.categories.push({
                    id: null,
                    level: this.data.id,
                    mdl_category: null,
                    subcategories: true,
                });
            },
            goToLevelList() {
                this.$router.push({name: 'admin-level-list'});
            },
            save() {
                let categories = _.map(this.selectedCategories, function (category) {
                    return {
                        categoryid: category.id,
                        mdlcategory: category.mdl_category,
                        subcategories: category.subcategories,
                    };
                });
                let result = {
                    levelid: (this.data.id || 0),
                    name: this.data.name,
                    bgcolor: this.data.bgcolor,
                    fgcolor: this.data.fgcolor,
                    categories: categories,
                    image: this.data.image,
                    imgmimetype: (this.imageMimetype ? this.imageMimetype : ''),
                    imgcontent: (this.imageContent ? this.imageContent : ''),
                };
                this.saving = true;
                this.saveLevel(result)
                    .then((successful) => {
                        this.saving = false;
                        if (successful) {
                            this.goToLevelList();
                        }
                    });
            },
            onImageSelected(event) {
                let file = event.target.files[0];
                if (file.type.startsWith('image/')) {
                    this.imageFilename = file.name;
                    let reader = new FileReader();
                    reader.onloadend = function () {
                        let result = reader.result.split(',');
                        this.imageMimetype = result[0].replace('data:', '').replace(';base64', '');
                        this.imageContent = result[1];
                    }.bind(this);
                    reader.readAsDataURL(file);
                }
            },
        },
        mounted() {
            this.fetchMdlCategories();
            this.initLevelData(this.level);
        },
        watch: {
            level: function (level) {
                this.initLevelData(level);
            },
            levelCategories: function (levelCategories) {
                this.categories = levelCategories;
            },
        },
        components: {
            Level,
            loadingAlert,
            btnAdd,
            VkNotification,
        },
    }
</script>
