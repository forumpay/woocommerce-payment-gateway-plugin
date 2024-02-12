import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'forumpay_data', {} );

const label = decodeEntities( settings.title || 'Pay with Crypto');

/**
 * Content component
 */
const Content = () => {
  return decodeEntities( settings.description || 'Pay with Crypto (by ForumPay)' );
};

/**
 * Label component
 *
 * @param {*} props Props from payment API.
 */
const Label = ( props ) => {
  const { PaymentMethodLabel } = props.components;
  return <PaymentMethodLabel text={ label } />;
};

/**
 * ForumPay payment method config object.
 */
const ForumPay = {
  name: "forumpay",
  label: <Label />,
  content: <Content />,
  edit: <Content />,
  canMakePayment: () => true,
  ariaLabel: label,
  supports: {
    features: settings.supports,
  },
};

registerPaymentMethod( ForumPay );
