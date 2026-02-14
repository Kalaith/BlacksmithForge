import React from 'react';
import { useGameDataContext } from '../../providers/GameDataProvider';

interface CustomersTabProps {
  active: boolean;
}

const CustomersTab: React.FC<CustomersTabProps> = ({ active }) => {
  const { customers } = useGameDataContext();

  if (!active) return null;

  return (
    <section id="customers-tab" className="tab-content active">
      <div className="customers-container">
        <h2>👥 Customers</h2>
        <p className="text-muted">
          Customer transactions are handled by the backend. Browse available customer types below.
        </p>

        <div className="customers-grid">
          {customers.map((customer, idx) => (
            <div key={`${customer.name}-${idx}`} className="customer-card">
              <div className="customer-header">
                <div className="customer-info">
                  <span className="customer-icon">{customer.icon}</span>
                  <div className="customer-details">
                    <div className="customer-name">{customer.name}</div>
                    <div className="customer-type">{customer.preferences}</div>
                  </div>
                </div>
              </div>

              <div className="customer-stats">
                <div className="stat">
                  <span className="stat-label">Budget:</span>
                  <span className="stat-value">{customer.budget}g</span>
                </div>
                <div className="stat">
                  <span className="stat-label">Reputation:</span>
                  <span className="stat-value">{customer.reputation}</span>
                </div>
              </div>
            </div>
          ))}
          {customers.length === 0 && <div className="no-customers">No customers available.</div>}
        </div>
      </div>
    </section>
  );
};

export default CustomersTab;
