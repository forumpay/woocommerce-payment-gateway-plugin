<script>

const DateInput = {
  props: {
    modelValue: {
      type: String,
      required: false,
    },
    label: {
      type: String,
      default: 'Select a date:',
    },
    computedDate: {
      type: String,
      required: false,
    },
    isRequired: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data() {
    return {
      selectedDay: '',
      selectedMonth: '',
      selectedYear: '',
      backupRequired: false,
    };
  },
  computed: {
    currentYear() {
      return new Date().getFullYear();
    },
    years() {
      const years = [];
      for (let i = this.currentYear; i >= 1900; i -= 1) {
        years.push(i);
      }
      return years;
    },
    months() {
      return Array.from({ length: 12 }, (_, index) => index + 1);
    },
    days() {
      let daysInMonth = 31;
      if (this.selectedMonth) {
        if ([4, 6, 9, 11].includes(this.selectedMonth)) {
          daysInMonth = 30;
          if (this.selectedDay === 31) {
            this.selectedDay = 30;
          }
        } else if (this.selectedMonth === 2) {
          daysInMonth = this.isLeapYear(this.selectedYear) ? 29 : 28;
          if (this.selectedDay === 31 || this.selectedDay === 30 || this.selectedDay === 29) {
            this.selectedDay = daysInMonth;
          }
        }
      }

      return Array.from({ length: daysInMonth }, (_, index) => index + 1);
    },
  },
  watch: {
    computedDate: {
      immediate: true,
      handler(newValue) {
        this.setInitialDate(newValue);
      },
    },
    selectedDay() {
      if (this.isValidDate()) {
        this.updateDate();
      }
    },
    selectedMonth() {
      if (this.isValidDate()) {
        this.updateDate();
      }
    },
    selectedYear() {
      if (this.isValidDate()) {
        this.updateDate();
      }
    },
  },
  methods: {
    setInitialDate(dateString) {
      if (dateString) {
        const [year, month, day] = dateString.split('-');
        this.selectedYear = parseInt(year, 10);
        this.selectedMonth = parseInt(month, 10);
        this.selectedDay = parseInt(day, 10);
      }
    },
    isLeapYear(year) {
      return (year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0));
    },
    isDateRequired() {
      if (this.isRequired) {
        return true;
      }
      return !!this.selectedDay || !!this.selectedMonth || !!this.selectedYear;
    },
    isValidDate() {
      if (!this.selectedDay || !this.selectedMonth || !this.selectedYear) {
        return false;
      }
      const date = new Date(this.selectedYear, this.selectedMonth - 1, this.selectedDay);
      return (
        date.getFullYear() === this.selectedYear
        && date.getMonth() === this.selectedMonth - 1
        && date.getDate() === this.selectedDay
      );
    },
    updateDate() {
      const formattedDate = `${this.selectedYear}-${String(this.selectedMonth).padStart(2, '0')}-${String(this.selectedDay).padStart(2, '0')}`;
      this.$emit('update:modelValue', formattedDate);
    },
    formatSingleDigitNumber(day) {
      return day < 10 ? `0${day}` : day.toString();
    },
  },
};

export default DateInput;
</script>

<template>
  <div>
    <p class="forumpay-pgw-dropdown-select-label">
      {{ label }} <span v-if="!isRequired">(optional)</span>
    </p>
    <div class="forumpay-pgw-dob">
      <div class="forumpay-pgw-dropdown-select forumpay-pgw-dob-field forumpay-pgw-dob-field-year">
        <select
          v-model="selectedYear"
          class="forumpay-pgw-dropdown-select-field"
          :class="{ 'forumpay-pgw-dropdown-select-field-default': selectedYear === '' }"
          :required="isDateRequired()"
          aria-label="year"
        >
          <option value="" :disabled="isRequired">
            YYYY
          </option>
          <option v-for="year in years" :key="year" :value="year">
            {{ year }}
          </option>
        </select>
      </div>
      <div class="forumpay-pgw-dropdown-select forumpay-pgw-dob-field">
        <select
          v-model="selectedMonth"
          class="forumpay-pgw-dropdown-select-field"
          :class="{ 'forumpay-pgw-dropdown-select-field-default': selectedMonth === '' }"
          :required="isDateRequired()"
          aria-label="month"
        >
          <option value="" :disabled="isRequired">
            MM
          </option>
          <option v-for="month in months" :key="month" :value="month">
            {{ formatSingleDigitNumber(month) }}
          </option>
        </select>
      </div>
      <div class="forumpay-pgw-dropdown-select forumpay-pgw-dob-field">
        <select
          v-model="selectedDay"
          class="forumpay-pgw-dropdown-select-field"
          :class="{ 'forumpay-pgw-dropdown-select-field-default': selectedDay === '' }"
          :required="isDateRequired()"
          aria-label="day"
        >
          <option value="" :disabled="isRequired">
            DD
          </option>
          <option v-for="day in days" :key="day" :value="day">
            {{ formatSingleDigitNumber(day) }}
          </option>
        </select>
      </div>
    </div>
  </div>
</template>
