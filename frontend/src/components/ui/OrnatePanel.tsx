import React from 'react';

interface OrnatePanelProps {
  title?: string;
  icon?: React.ReactNode;
  className?: string;
  variant?: 'primary' | 'secondary' | 'tertiary';
  children: React.ReactNode;
}

const OrnatePanel: React.FC<OrnatePanelProps> = ({
  title,
  icon,
  className = '',
  variant = 'secondary',
  children,
}) => (
  <section className={`ornate-panel ornate-panel--${variant} ${className}`.trim()}>
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
