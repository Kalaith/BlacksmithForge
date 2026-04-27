import React from 'react';
import { Customer } from '../../types/game.d';
import CrestIcon from '../ui/CrestIcon';
import FramedButton from '../ui/FramedButton';
import OrnatePanel from '../ui/OrnatePanel';

interface CustomerPreviewPanelProps {
  customers: Customer[];
  onViewAll: () => void;
}

const getIntentPresentation = (
  preferences: string
): { icon: string; label: string; tone: 'durability' | 'value' | 'quality' } => {
  const normalized = preferences.toLowerCase();

  if (normalized.includes('durability')) {
    return { icon: '🛡️', label: 'Durability', tone: 'durability' };
  }

  if (normalized.includes('quality')) {
    return { icon: '✦', label: 'Quality', tone: 'quality' };
  }

  return { icon: '🪙', label: 'Value', tone: 'value' };
};

const CustomerPreviewPanel: React.FC<CustomerPreviewPanelProps> = ({ customers, onViewAll }) => (
  <OrnatePanel
    title="Customers"
    icon="📌"
    className="dashboard-panel dashboard-panel--customers"
    variant="secondary"
  >
    <p className="dashboard-copy">Fulfill orders and build your reputation.</p>
    <div className="dashboard-list">
      {customers.slice(0, 3).map(customer => {
        const intent = getIntentPresentation(customer.preferences);
        return (
        <div key={`${customer.name}-${customer.preferences}`} className="dashboard-row customer-preview-row">
          <div className="dashboard-row__identity">
            <CrestIcon icon={customer.icon} label={customer.name} />
            <div>
              <div className="dashboard-row__title">{customer.name}</div>
              <div className="dashboard-row__meta">
                {customer.description || customer.preferences}
              </div>
              <span className={`intent-badge intent-badge--${intent.tone}`}>
                {intent.icon} {intent.label}
              </span>
            </div>
          </div>
          <div className="dashboard-row__stats">
            <span>
              <small>Budget</small>
              {customer.budget}g
            </span>
            <span>
              <small>Reputation</small>
              {customer.reputation}
            </span>
          </div>
        </div>
        );
      })}
    </div>
    <FramedButton icon="👥" onClick={onViewAll}>
      View All Customers
    </FramedButton>
  </OrnatePanel>
);

export default CustomerPreviewPanel;
