<script>

const Dropdown = {
  props: {
    label: { type: String },
    options: { type: Array },
    filterProperty: { type: String },
    optionKey: { type: String }, // only needed when modelValue is string
    modelValue: { type: [Object, String] },
  },
  emits: ['update:modelValue'],
  data() {
    return {
      isActive: false,
      focused: false,
      searchValue: '',
      optionsFiltered: this.options,
      activeOption: this.options[0],
      selectedOption: null,
    };
  },
  mounted() {
    this.selectedOption = this.optionKey
      ? this.options.find((option) => option[this.optionKey] === this.modelValue)
      : this.modelValue;
  },
  watch: {
    isActive(active) {
      if (active) {
        this.optionsFiltered = this.options;
        this.$refs.input.focus();

        if (this.selectedOption) {
          this.activeOption = this.selectedOption;
          return;
        }
        [this.activeOption] = this.options;
      } else {
        this.searchValue = '';
      }
    },
    selectedOption(option) {
      if (option) {
        this.searchValue = '';
        this.$emit('update:modelValue', this.optionKey ? option[this.optionKey] : option);
      } else {
        this.$emit('update:modelValue', this.optionKey ? this.options[0][this.optionKey] : this.options[0]);
      }
    },
  },
  methods: {
    open() {
      this.isActive = !this.isActive;
    },
    close() {
      this.isActive = false;
    },
    onEnter() {
      if (this.isActive) {
        this.selectedOption = this.activeOption;
      }
      this.close();
    },
    onDelete() {
      this.selectedOption = null;
    },
    onKeypress(event) {
      if (this.selectedOption) {
        event.preventDefault();
      }
    },
    onInput() {
      if (!this.isActive) {
        this.open();
      }

      const filterPossible = this.options.some((option) => option[this.filterProperty]
        .toLowerCase()
        .includes(this.searchValue.toLowerCase()));
      if (!filterPossible) {
        this.optionsFiltered = null;
        return;
      }
      this.optionsFiltered = this.options.filter((option) => option[this.filterProperty]
        .toLowerCase()
        .includes(this.searchValue.toLowerCase()));

      const optionExists = this.optionsFiltered
        .some((option) => option[this.filterProperty] === this.activeOption[this.filterProperty]);
      if (!optionExists) {
        [this.activeOption] = this.optionsFiltered;
      }
    },
    onUp() {
      if (!this.isActive || !this.optionsFiltered) {
        return;
      }

      const index = this.optionsFiltered.findIndex(
        (option) => option[this.filterProperty] === this.activeOption[this.filterProperty],
      );
      if (this.optionsFiltered[index - 1]) {
        this.activeOption = this.optionsFiltered[index - 1];
      }
    },
    onDown() {
      if (!this.isActive || !this.optionsFiltered) {
        this.open();
        return;
      }

      const index = this.optionsFiltered.findIndex(
        (option) => option[this.filterProperty] === this.activeOption[this.filterProperty],
      );
      if (this.optionsFiltered[index + 1]) {
        this.activeOption = this.optionsFiltered[index + 1];
      }
    },
    onOptionClick() {
      this.selectedOption = this.activeOption;
      this.close();
    },
    onOptionHover(option) {
      this.activeOption = option;
    },
    markText(text) {
      if (this.searchValue === '') {
        return text;
      }
      const regex = new RegExp(this.searchValue, 'gi');
      return text.replace(regex, '<mark>$&</mark>');
    },
  },
  computed: {
    placeholder() {
      if (!this.selectedOption && this.options.length) {
        return this.options[0][this.filterProperty];
      }
      return null;
    },
  },
  updated() {
    if (this.isActive && this.optionsFiltered) {
      const el = this.$el.getElementsByClassName('active-field')[0];
      el?.scrollIntoView({ block: 'nearest' });
    }
  },
};

export default Dropdown;

</script>

<template>
  <div
    v-click-outside="close"
    class="forumpay-pgw-dropdown"
  >
    <span
      v-if="label"
      class="forumpay-pgw-dropdown-label"
    >{{ label }}</span>
    <div
      class="forumpay-pgw-dropdown-search"
      :class="{ 'dropdown-outline': focused }"
      role="button"
      tabIndex="0"
      @keyup="open"
      @click="open"
    >
      <div
        v-if="selectedOption"
        class="forumpay-pgw-dropdown-selected"
      >
        <slot
          name="selected"
          :selected="selectedOption"
        />
      </div>
      <input
        ref="input"
        v-model="searchValue"
        type="text"
        :placeholder="placeholder"
        aria-label="search"
        @input="onInput"
        @keyup.enter="onEnter"
        @keyup.delete="onDelete"
        @keydown.up="onUp"
        @keydown.down="onDown"
        @keydown.esc="close"
        @keydown.tab="close"
        @keypress="onKeypress($event)"
        @focus="focused = true"
        @blur="focused = false"
      >
    </div>
    <div
      v-if="isActive"
      class="forumpay-pgw-dropdown-list"
    >
      <div
        v-for="option in optionsFiltered"
        :key="option"
        class="forumpay-pgw-dropdown-field"
        :class="{ 'active-field': option[filterProperty] === activeOption[filterProperty] }"
        role="button"
        tabIndex="0"
        @keyup="onOptionClick"
        @click="onOptionClick"
        @mouseover="onOptionHover(option)"
        @focus="onOptionHover(option)"
      >
        <slot
          name="option"
          :option="option"
          :mark-text="markText"
        />
      </div>
    </div>
  </div>
</template>
