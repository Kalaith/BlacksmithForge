import React from 'react';

interface OrnatePanelProps {
  title?: string;
  icon?: React.ReactNode;
  className?: string;
  children: React.ReactNode;
}

const OrnatePanel: React.FC<OrnatePanelProps> = ({ title, icon, className = '', children }) => (
  <section className={`ornate-panel ${className}`.trim()}>
    {title ? (
      <header className="ornate-panel__header">
        {icon ? <span className="ornate-panel__icon">{icon}</span> : null}
        <h2>{title}</h2>
      </header>
    ) : null}
    <div className="ornate-panel__body">{children}</div>
  </section>
);

export default OrnatePanel;

