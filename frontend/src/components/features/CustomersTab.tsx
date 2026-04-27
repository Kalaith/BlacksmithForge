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
        <div className="detail-heading">
          <span>👥</span>
          <div>
            <h2>Customers</h2>
            <p>Customer transactions are handled by the backend. Browse available customer types below.</p>
          </div>
        </div>

        <div className="customer-ledger">
          {customers.map((customer, idx) => (
            <article key={`${customer.name}-${idx}`} className="customer-ledger-row">
              <div className="customer-ledger-row__identity">
                <span className="crest-icon customer-ledger-row__icon">{customer.icon}</span>
                <div>
                  <h3>{customer.name}</h3>
                  <p>{customer.description || customer.preferences}</p>
                  <span className={`intent-badge intent-badge--${customer.preferences}`}>
                    {customer.preferences === 'durability'
                      ? '🛡️ Durability'
                      : customer.preferences === 'quality'
                        ? '✦ Quality'
                        : '🪙 Value'}
                  </span>
                </div>
              </div>

              <div className="customer-ledger-row__stats">
                <span>
                  <small>Budget</small>
                  {customer.budget}g
                </span>
                <span>
                  <small>Reputation</small>
                  {customer.reputation}
                </span>
              </div>
            </article>
          ))}
          {customers.length === 0 && <div className="no-customers">No customers available.</div>}
        </div>
      </div>
    </section>
  );
};

export default CustomersTab;
