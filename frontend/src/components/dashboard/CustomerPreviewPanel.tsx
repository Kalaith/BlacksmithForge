import React from 'react';
import { Customer } from '../../types/game.d';
import CrestIcon from '../ui/CrestIcon';
import FramedButton from '../ui/FramedButton';
import OrnatePanel from '../ui/OrnatePanel';

interface CustomerPreviewPanelProps {
  customers: Customer[];
  onViewAll: () => void;
}

const CustomerPreviewPanel: React.FC<CustomerPreviewPanelProps> = ({ customers, onViewAll }) => (
  <OrnatePanel title="Customers" icon="👥" className="dashboard-panel dashboard-panel--customers">
    <p className="dashboard-copy">Fulfill orders and build your reputation.</p>
    <div className="dashboard-list">
      {customers.slice(0, 5).map(customer => (
        <div key={`${customer.name}-${customer.preferences}`} className="dashboard-row">
          <div className="dashboard-row__identity">
            <CrestIcon icon={customer.icon} label={customer.name} />
            <div>
              <div className="dashboard-row__title">{customer.name}</div>
              <div className="dashboard-row__meta">{customer.preferences}</div>
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
      ))}
    </div>
    <FramedButton icon="👥" onClick={onViewAll}>
      View All Customers
    </FramedButton>
  </OrnatePanel>
);

export default CustomerPreviewPanel;

