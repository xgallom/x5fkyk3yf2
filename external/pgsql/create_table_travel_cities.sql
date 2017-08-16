-- Table: public.travel_cities

-- DROP TABLE public.travel_cities;

CREATE TABLE public.travel_cities
(
  id serial NOT NULL,
  travel_id integer NOT NULL,
  travel_step integer NOT NULL DEFAULT 1,
  city_id integer NOT NULL,
  CONSTRAINT travel_cities_pkey PRIMARY KEY (id),
  CONSTRAINT travel_cities_fkey_city_id FOREIGN KEY (city_id)
  REFERENCES public.city (id) MATCH SIMPLE
  ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT travel_cities_fkey_travel_id FOREIGN KEY (travel_id)
  REFERENCES public.travel (id) MATCH SIMPLE
  ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
OIDS=FALSE
);
ALTER TABLE public.travel_cities
OWNER TO "o2-carpool";
